<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayFee;
use App\Models\SysdevWithdraw;
use App\Models\XenditWebhookLog;
use App\Services\XenditFeeCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(
        private XenditFeeCalculatorService $feeCalculator
    ) {}

    // =========================================================================
    // UNIFIED ENTRY POINT
    // POST /api/webhook/xendit  (standar — didaftarkan di Xendit Dashboard)
    // POST /api/webhook/xendit/invoice       } alias — backward compat
    // POST /api/webhook/xendit/disbursement  }
    // =========================================================================

    /**
     * Unified dispatcher — deteksi tipe webhook dari field 'event' di payload:
     *   • Ada 'event' (mis. "disbursement.completed") → handleDisbursement()
     *   • Tidak ada 'event' → handleInvoice()  (format lama Xendit Invoice)
     */
    public function handle(Request $request): JsonResponse
    {
        $unauthorized = $this->validateToken($request);
        if ($unauthorized) return $unauthorized;

        $payload = $request->all();
        $event   = $payload['event'] ?? null;

        Log::info('[XenditWebhook] Payload diterima di unified endpoint.', [
            'event'       => $event ?? '(invoice — no event field)',
            'external_id' => $payload['external_id'] ?? ($payload['data']['reference_id'] ?? null),
        ]);

        // Disbursement: Xendit v2 selalu mengirim field 'event'
        if ($event !== null) {
            return $this->handleDisbursement($request);
        }

        // Fallback: Invoice (tidak ada field 'event')
        return $this->handleInvoice($request);
    }

    /**
     * Validasi x-callback-token dari Xendit.
     * Return JsonResponse 401 jika gagal, null jika valid.
     */
    private function validateToken(Request $request): ?JsonResponse
    {
        $callbackToken = $request->header('x-callback-token');
        $liveToken     = config('services.xendit.webhook_token_live');
        $testToken     = config('services.xendit.webhook_token_test');

        if (empty($callbackToken) || ($callbackToken !== $liveToken && $callbackToken !== $testToken)) {
            Log::warning('[XenditWebhook] Token tidak valid.', ['received' => $callbackToken]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return null;
    }

    /**
     * Menerima webhook Invoice dari Xendit.
     * Dipanggil oleh handle() atau langsung via alias route.
     */
    private function handleInvoice(Request $request): JsonResponse
    {
        // Token sudah divalidasi oleh handle(), tapi jika dipanggil via alias
        // route lama, validasi ulang agar tetap aman.
        $unauthorized = $this->validateToken($request);
        if ($unauthorized) return $unauthorized;

        $payload = $request->all();
        $externalId    = $payload['external_id'] ?? null;
        $status        = $payload['status'] ?? null;
        $amount        = (int) ($payload['amount'] ?? 0);
        $paymentMethod = $payload['payment_method'] ?? ($payload['payment_channel'] ?? 'UNKNOWN');
        $bankCode      = $payload['bank_code'] ?? ($payload['payment_destination'] ?? null);
        $paidAt        = $payload['paid_at'] ?? ($payload['updated'] ?? null);

        if (!$externalId || !$status) {
            return response()->json(['message' => 'Payload tidak valid: external_id dan status wajib ada.'], 422);
        }

        // 2. Deteksi aplikasi klien dari prefix external_id
        $appId = $this->detectAppId($externalId);

        Log::info('[XenditWebhook] Deteksi app_id', [
            'external_id' => $externalId,
            'prefix'      => strtoupper(explode('-', str_replace('/', '-', $externalId))[0]),
            'app_id'      => $appId,
            'status'      => $status,
        ]);

        // 3. Ambil data fee & nama app
        // Jika appId terdeteksi: query by app_id langsung
        // Jika tidak: cari secara fallback lewat semua PaymentGatewayFee record
        $feeRecord = $appId
            ? PaymentGatewayFee::where('app_id', $appId)->first()
            : $this->findFeeRecordByExternalId($externalId);

        // Jika feeRecord ditemukan via fallback tapi appId masih null, sinkronkan
        if (!$appId && $feeRecord) {
            $appId = $feeRecord->app_id;
            Log::info('[XenditWebhook] app_id ditemukan via fallback DB', [
                'external_id' => $externalId,
                'app_id'      => $appId,
            ]);
        }

        $appName = $feeRecord?->app_name ?? $appId ?? 'unknown';

        // 4. Hitung fee & Estimasi Settlement Date (hanya untuk status terminal: PAID, SETTLED)
        $fees = [
            'fee_pg' => 0,
            'fee_sysdev' => 0,
            'withdrawable' => 0,
            'settlement_days' => 0,
            'estimated_settlement_date' => null,
        ];

        if (in_array($status, ['PAID', 'SETTLED']) && $amount > 0 && $appId) {
            $calculated = $this->feeCalculator->calculate($paymentMethod, $appId, $amount);
            $fees['fee_pg'] = $calculated['fee_pg'];
            $fees['fee_sysdev'] = $calculated['fee_sysdev'];
            $fees['withdrawable'] = $calculated['withdrawable'];

            // Tentukan jumlah hari kerja untuk settlement (Hanya untuk metode QR, 3 hari kerja)
            $addDays = match (true) {
                str_contains(strtoupper($paymentMethod), 'QR') => 3,
                default => 0,
            };
            $fees['settlement_days'] = $addDays;

            // Hitung tanggal settlement dengan mengabaikan weekend & hari libur
            if ($paidAt) {
                try {
                    $paidDate = \Carbon\Carbon::parse($paidAt);
                    if ($addDays > 0) {
                        // Tambah hari kerja dan set jam ke 23:59:59
                        $settlementDate = \App\Models\Holiday::addWorkingDays($paidDate, $addDays)->setTime(23, 59, 59);
                    } else {
                        // Jika bukan QR, settlement dianggap instant (waktu bayar)
                        $settlementDate = $paidDate;
                    }
                    $fees['estimated_settlement_date'] = $settlementDate->format('Y-m-d H:i:s');
                } catch (\Throwable $e) {
                    Log::error('[XenditWebhook] Gagal hitung settlement date: ' . $e->getMessage());
                }
            }
        }

        // 5. Catat ke log
        $log = XenditWebhookLog::updateOrCreate(
            ['external_id' => $externalId],
            [
                'app_id'        => $appId,
                'app_name'      => $appName,
                'status'        => $status,
                'payment_method' => $paymentMethod,
                'bank_code'     => $bankCode,
                'amount'        => $amount,
                'fee_pg'        => $fees['fee_pg'],
                'fee_sysdev'    => $fees['fee_sysdev'],
                'withdrawable'  => $fees['withdrawable'],
                'raw_payload'   => $payload,
                'paid_at'       => $paidAt,
            ]
        );

        // 6. Forward ke child app
        // Jika app_id masih null tapi feeRecord tersedia lewat DB fallback, pakai dari sana
        $forwardUrl      = $feeRecord?->internal_webhook_url ?: null;
        $appIsProduction = $feeRecord?->isProduction() ?? false;

        if ($forwardUrl) {
            $this->forwardToChildApp($log, $forwardUrl, $payload, $fees, $appIsProduction);
        } else {
            Log::warning('[XenditWebhook] Tidak ada forward URL.', [
                'app_id'      => $appId,
                'external_id' => $externalId,
                'fee_record'  => $feeRecord?->app_id,
            ]);
        }

        return response()->json(['status' => 'received'], 200);
        // return response()->json(['status' => 'received'], 200);
    }

    /**
     * Deteksi app_id dari prefix external_id.
     * Fast path: hardcoded prefix map.
     */
    private function detectAppId(string $externalId): ?string
    {
        $upper = strtoupper($externalId);

        // e-yac / archery
        if (str_starts_with($upper, 'YISC/ARCH') || str_starts_with($upper, 'DISB/ARCH')) {
            return 'e-yac';
        }

        if  (str_starts_with($upper, 'DISB/SYS')) {
            return 'app';
        }


        // PPAB
        if (str_starts_with($upper, 'YISC/PPAB') || str_starts_with($upper, 'PPAB') || str_starts_with($upper, 'YISCAL')) {
            return 'join-ppab';
        }

        // e-sii
        if (str_starts_with($upper, 'SII') || str_starts_with($upper, 'ESII')) {
            return 'e-sii';
        }

        // Fallback untuk awalan YISC (historis YISC-xxx masuk ke PPAB)
        // if (str_starts_with($upper, 'YISC')) {
        //     return 'join-ppab';
        // }

        return null;
    }

    /**
     * Fallback: cari PaymentGatewayFee record berdasarkan external_id.
     * Strategi: pecah app_id per segmen (e.g. "join-ppab" → ["join","ppab"]),
     * cek apakah salah satu segmen (min 3 karakter) ada dalam external_id.
     * Ini cocok untuk format external_id apapun selama mengandung kata kunci.
     */
    private function findFeeRecordByExternalId(string $externalId): ?PaymentGatewayFee
    {
        try {
            $externalIdUpper = strtoupper($externalId);

            return PaymentGatewayFee::all()->first(function (PaymentGatewayFee $fee) use ($externalIdUpper) {
                if (!$fee->app_id) return false;

                // Pecah app_id menjadi segmen: "join-ppab" → ["JOIN", "PPAB"]
                $parts = explode('-', strtoupper($fee->app_id));
                foreach ($parts as $part) {
                    // Hanya cek segmen yang spesifik (min 3 karakter) untuk hindari false positive
                    if (strlen($part) >= 3 && str_contains($externalIdUpper, $part)) {
                        return true;
                    }
                }
                return false;
            });
        } catch (\Throwable $e) {
            Log::error('[XenditWebhook] Gagal cari fee record via fallback: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve URL forward dari record DB (internal_webhook_url).
     */
    private function resolveForwardUrl(?string $appId): ?string
    {
        if (!$appId) return null;

        $record = PaymentGatewayFee::where('app_id', $appId)->first();
        return $record?->internal_webhook_url ?: null;
    }

    /**
     * Kirim HTTP POST ke internal webhook child app dengan Bearer Token.
     * Menggunakan flat payload dan X-Internal-Token untuk backward compatibility.
     */
    private function forwardToChildApp(
        XenditWebhookLog $log,
        string $forwardUrl,
        array $payload,
        array $fees,
        bool $isProduction
    ): void {
        try {
            // Gabung $payload ke top-level (flat) agar child app lama (seperti e-yac) bisa langsung baca
            $postData = array_merge($payload, [
                'xendit_payload'            => $payload,
                'calculated_fees'           => $fees,
                'environment'               => $isProduction ? 'production' : 'development',
                'settlement_days'           => $fees['settlement_days'] ?? 0,
                'estimated_settlement_date' => $fees['estimated_settlement_date'] ?? null,
            ]);

            $response = Http::timeout(10)
                ->withToken(config('services.internal.secret'))
                ->withHeaders([
                    'X-Internal-Token' => config('services.internal.secret') // Kompatibilitas E-YAC
                ])
                ->post($forwardUrl, $postData);

            $log->update([
                'forward_url'      => $forwardUrl,
                'forward_status'   => $response->status(),
                'forward_response' => (string) $response->body(),
            ]);

            Log::info("[XenditWebhook] Forward ke {$forwardUrl}: HTTP {$response->status()}");
        } catch (\Throwable $e) {
            $log->update([
                'forward_url'      => $forwardUrl,
                'forward_status'   => null,
                'forward_response' => $e->getMessage(),
            ]);

            Log::error("[XenditWebhook] Gagal forward ke {$forwardUrl}: " . $e->getMessage());
        }
    }

    // =========================================================================
    // DISBURSEMENT WEBHOOK HANDLER
    // Menangani callback Xendit untuk kode DISB/SYS dan DISB/ARCH.
    // Endpoint: POST /api/webhook/xendit/disbursement
    // =========================================================================

    /**
     * Menerima webhook Disbursement dari Xendit.
     * Dipanggil oleh handle() atau langsung via alias route.
     *
     * Payload Xendit Disbursement v2:
     * { "event": "disbursement.completed", "data": { "reference_id": "DISB/SYS-xxx", ... } }
     */
    private function handleDisbursement(Request $request): JsonResponse
    {
        // Token sudah divalidasi oleh handle(), tapi validasi ulang jika dipanggil via alias route.
        $unauthorized = $this->validateToken($request);
        if ($unauthorized) return $unauthorized;

        // Ekstrak data inner dari Xendit Disbursement v2 (dibungkus di key 'data')
        $payload = $request->all();
        $data    = $payload['data'] ?? $payload; // fallback jika tidak ada wrapping

        // PENTING: gunakan idempotency_key (nilai yang kita set saat buat disbursement)
        // bukan reference_id (itu Xendit internal ID seperti "disb-xxx")
        // Di DB kita, kolom reference_id menyimpan nilai idempotency_key.
        $referenceId = $data['idempotency_key'] ?? ($data['reference_id'] ?? null);
        $status      = $data['status'] ?? null;

        if (!$referenceId || !$status) {
            Log::warning('[XenditDisbursementWebhook] Payload tidak valid.', ['payload' => $payload]);
            return response()->json(['message' => 'Payload tidak valid: idempotency_key/reference_id dan status wajib ada.'], 422);
        }

        Log::info('[XenditDisbursementWebhook] Diterima.', [
            'idempotency_key'      => $data['idempotency_key'] ?? null,
            'xendit_reference_id'  => $data['reference_id'] ?? null,
            'resolved_reference'   => $referenceId,
            'status'               => $status,
        ]);

        // Routing berdasarkan prefix
        $upper = strtoupper($referenceId);

        if (str_contains($upper, 'DISB/SYS')) {
            $this->sysdevDisbursement($data);
        } elseif (str_contains($upper, 'DISB/ARCH')) {
            $this->archeryDisbursement($data);
        } else {
            Log::warning('[XenditDisbursementWebhook] Prefix tidak dikenal, tidak ada aksi.', [
                'reference_id' => $referenceId,
            ]);
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Update status disbursement DISB/SYS di tabel sysdev_withdraws (DB: ppab).
     * Menggunakan idempotency_key sebagai key pencarian ke kolom reference_id.
     */
    private function sysdevDisbursement(array $data): void
    {
        // idempotency_key === reference_id yang di-set saat membuat disbursement
        $referenceId = $data['idempotency_key'] ?? ($data['reference_id'] ?? null);

        if (!$referenceId) {
            Log::error('[XenditDisbursementWebhook] sysdev: idempotency_key/reference_id kosong.');
            return;
        }

        $estimatedArrivalTime = null;
        if (!empty($data['estimated_arrival_time'])) {
            $estimatedArrivalTime = \Carbon\Carbon::parse($data['estimated_arrival_time'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        }

        $update = [
            'estimated_arrival_time' => $estimatedArrivalTime,
            'status'                 => $data['status'],
        ];

        // Tambah failure_code jika status bukan terminal sukses
        if (!in_array($data['status'], ['SUCCEEDED', 'ACCEPTED'])) {
            $update['failure_code'] = $data['failure_code'] ?? null;
        }

        try {
            $affected = SysdevWithdraw::where('reference_id', $referenceId)->update($update);
            Log::info('[XenditDisbursementWebhook] sysdev_withdraws diupdate.', [
                'reference_id' => $referenceId,
                'status'       => $data['status'],
                'affected'     => $affected,
            ]);
        } catch (\Throwable $e) {
            Log::error('[XenditDisbursementWebhook] Gagal update sysdev_withdraws: ' . $e->getMessage(), [
                'reference_id' => $referenceId,
            ]);
        }
    }

    /**
     * Update status disbursement DISB/ARCH di tabel archery_withdraws (DB: ppab).
     * Menggunakan idempotency_key sebagai key pencarian ke kolom reference_id.
     */
    private function archeryDisbursement(array $data): void
    {
        $referenceId = $data['idempotency_key'] ?? ($data['reference_id'] ?? null);

        if (!$referenceId) {
            Log::error('[XenditDisbursementWebhook] archery: idempotency_key/reference_id kosong.');
            return;
        }

        $estimatedArrivalTime = null;
        if (!empty($data['estimated_arrival_time'])) {
            $estimatedArrivalTime = \Carbon\Carbon::parse($data['estimated_arrival_time'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        }

        $update = [
            'estimated_arrival_time' => $estimatedArrivalTime,
            'status'                 => $data['status'],
        ];

        if (!in_array($data['status'], ['SUCCEEDED', 'ACCEPTED'])) {
            $update['failure_code'] = $data['failure_code'] ?? null;
        }

        try {
            $affected = DB::connection('ppab')
                ->table('archery_withdraws')
                ->where('reference_id', $referenceId)
                ->update($update);

            Log::info('[XenditDisbursementWebhook] archery_withdraws diupdate.', [
                'reference_id' => $referenceId,
                'status'       => $data['status'],
                'affected'     => $affected,
            ]);
        } catch (\Throwable $e) {
            Log::error('[XenditDisbursementWebhook] Gagal update archery_withdraws: ' . $e->getMessage(), [
                'reference_id' => $referenceId,
            ]);
        }
    }
}
