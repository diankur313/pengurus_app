<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PpabAttendance;
use App\Models\PpabParticipant;
use App\Models\PpabPayment;
use App\Models\PpabRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PpabScannerController extends Controller
{
    /**
     * Proses scan QR absensi peserta PPAB.
     *
     * Payload yang diterima:
     *   - member_uuid : UUID peserta dari QR code (wajib, string — bisa berupa UUID terpotong sesuai data di ppab_member)
     *
     * Session di-resolve otomatis dari id_session peserta di tabel ppab_member.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'member_uuid' => 'required|string|max:255',
        ]);

        $memberUuid  = trim($validated['member_uuid']);
        $sessionUuid = '';

        try {
            // 1. Validasi peserta berdasarkan uuid di tabel ppab_member
            $participant = PpabParticipant::where('uuid', $memberUuid)->first();

            if (! $participant) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'MEMBER_NOT_FOUND',
                    'message' => 'Peserta tidak ditemukan. Pastikan QR code sesuai.',
                ], 404);
            }

            // Pastikan peserta sudah melunasi pendaftaran
            if (! in_array($participant->stage, ['paid_payment', 'done'])) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'MEMBER_NOT_PAID',
                    'message' => 'Peserta belum melakukan pembayaran yang valid.',
                    'data'    => [
                        'name'  => $participant->name,
                        'stage' => $participant->stage,
                    ],
                ], 422);
            }

            // 2. Resolve sesi otomatis dari id_session peserta di ppab_member
            $sessionUuid = $participant->id_session ?? null;

            if (! $sessionUuid) {
                // Fallback: ambil sesi terbaru
                $session     = PpabRegistration::orderByDesc('id')->first();
                $sessionUuid = $session?->uuid;
            }

            if (! $sessionUuid) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'NO_ACTIVE_SESSION',
                    'message' => 'Tidak ada sesi PPAB aktif yang ditemukan.',
                ], 404);
            }

            $session = PpabRegistration::where('uuid', $sessionUuid)->first();

            // 3. Cek duplikat absensi untuk sesi ini
            $alreadyScanned = PpabAttendance::where('member_id', $memberUuid)
                ->where('session_id', $sessionUuid)
                ->exists();

            if ($alreadyScanned) {
                $payment = $this->getPaymentInfo($memberUuid, $sessionUuid);

                return response()->json([
                    'status'  => 'already_scanned',
                    'code'    => 'DUPLICATE_SCAN',
                    'message' => 'Peserta sudah tercatat hadir pada sesi ini.',
                    'data'    => [
                        'name'         => $participant->name,
                        'gender'       => $participant->gender,
                        'angkatan'     => $participant->angkatan,
                        'paket'        => $participant->paket,
                        'payment_type' => $payment['type'],
                        'payment_label' => $payment['label'],
                    ],
                ], 200);
            }

            // 4. Simpan record absensi
            // created_at di tabel ini bertipe VARCHAR — isi manual dengan string datetime
            $now = now()->toDateTimeString();
            PpabAttendance::create([
                'member_id'  => $memberUuid,
                'session_id' => $sessionUuid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 5. Ambil info pembayaran
            $payment = $this->getPaymentInfo($memberUuid, $sessionUuid);

            return response()->json([
                'status'  => 'success',
                'message' => 'Absensi berhasil dicatat!',
                'data'    => [
                    'name'          => $participant->name,
                    'gender'        => $participant->gender,
                    'angkatan'      => $participant->angkatan,
                    'paket'         => $participant->paket,
                    'payment_type'  => $payment['type'],
                    'payment_label' => $payment['label'],
                    'scanned_at'    => now()->toDateTimeString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('PpabScannerController::scan error', [
                'message'      => $e->getMessage(),
                'member_uuid'  => $memberUuid ?? null,
                'session_uuid' => $sessionUuid ?? null,
            ]);

            return response()->json([
                'status'  => 'error',
                'code'    => 'SERVER_ERROR',
                'message' => 'Terjadi kesalahan server. Coba lagi.',
            ], 500);
        }
    }

    /**
     * Cek status absensi peserta pada sesi tertentu.
     *
     * Query param: member_uuid, session_uuid (opsional)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'member_uuid'  => 'required|string',
            'session_uuid' => 'nullable|string',
        ]);

        $memberUuid  = trim($validated['member_uuid']);
        $sessionUuid = trim($validated['session_uuid'] ?? '');

        $participant = PpabParticipant::where('uuid', $memberUuid)->first();

        if (! $participant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Peserta tidak ditemukan.',
            ], 404);
        }

        if (empty($sessionUuid)) {
            $sessionUuid = $participant->id_session
                ?? PpabRegistration::orderByDesc('id')->value('uuid');
        }

        $attendance = PpabAttendance::where('member_id', $memberUuid)
            ->where('session_id', $sessionUuid)
            ->first();

        $payment = $this->getPaymentInfo($memberUuid, $sessionUuid);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'name'          => $participant->name,
                'gender'        => $participant->gender,
                'angkatan'      => $participant->angkatan,
                'paket'         => $participant->paket,
                'payment_type'  => $payment['type'],
                'payment_label' => $payment['label'],
                'is_present'    => (bool) $attendance,
                'scanned_at'    => $attendance?->created_at?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Ambil daftar absensi berdasarkan sesi.
     *
     * Query param: session_uuid (opsional — default sesi terbaru)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBySession(Request $request)
    {
        $sessionUuid = trim($request->query('session_uuid', ''));

        if (empty($sessionUuid)) {
            $sessionUuid = PpabRegistration::orderByDesc('id')->value('uuid');
        }

        if (! $sessionUuid) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak ada sesi aktif.',
            ], 404);
        }

        $attendances = PpabAttendance::with('participant')
            ->where('session_id', $sessionUuid)
            ->orderByDesc('created_at')
            ->get();

        $data = $attendances->map(function (PpabAttendance $attendance) use ($sessionUuid) {
            $participant = $attendance->participant;
            $payment     = $this->getPaymentInfo($attendance->member_id, $sessionUuid);

            return [
                'id'            => $attendance->id,
                'name'          => $participant?->name ?? '-',
                'gender'        => $participant?->gender ?? '-',
                'angkatan'      => $participant?->angkatan ?? '-',
                'paket'         => $participant?->paket ?? '-',
                'payment_type'  => $payment['type'],
                'payment_label' => $payment['label'],
                'scanned_at'    => $attendance->created_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'status'       => 'success',
            'session_uuid' => $sessionUuid,
            'total'        => $data->count(),
            'data'         => $data,
        ]);
    }

    /**
     * Helper: ambil informasi pembayaran peserta.
     */
    private function getPaymentInfo(string $memberUuid, string $sessionUuid): array
    {
        $payment = PpabPayment::where('id_member', $memberUuid)
            ->where('id_session', $sessionUuid)
            ->where('status', 'PAID')
            ->first();

        $type = $payment?->payment_type;

        return [
            'type'  => $type,
            'label' => match ($type) {
                'full' => 'Full Payment',
                'dp'   => 'Down Payment',
                default => '-',
            },
        ];
    }
}
