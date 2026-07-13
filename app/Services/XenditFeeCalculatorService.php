<?php

namespace App\Services;

use App\Models\PaymentGatewayFee;

class XenditFeeCalculatorService
{
    /**
     * Hitung fee berdasarkan payment_method dan data fee dari database.
     *
     * @param  string  $paymentMethod  BANK_TRANSFER | QR_CODE | RETAIL_OUTLET | VIRTUAL_ACCOUNT | EWALLET
     * @param  string  $appId          ID aplikasi (sesuai kolom app_id di paymentgatewayfees)
     * @param  int     $amount         Jumlah nominal pembayaran (dalam rupiah)
     * @return array{fee_pg: int, fee_sysdev: int, withdrawable: int}
     */
    public function calculate(string $paymentMethod, string $appId, int $amount): array
    {
        $feeRule = PaymentGatewayFee::where('app_id', $appId)->first();

        // Hitung fee PG Xendit berdasarkan metode pembayaran
        $feePg = $this->calculateXenditFee($paymentMethod, $amount, $feeRule);

        // Fee sysdev dari konfigurasi DB (flat)
        $feeSysdev = $feeRule ? (int) $feeRule->sysdev_fee : 0;

        // PPN 11% HANYA untuk metode Virtual Account / Bank Transfer
        $method = strtoupper($paymentMethod);
        $isVirtualAccount = str_contains($method, 'VIRTUAL_ACCOUNT') || str_contains($method, 'BANK_TRANSFER');
        
        $ppn = $feeRule ? (float) $feeRule->ppn : 0;
        if ($ppn > 0 && $isVirtualAccount) {
            $feePg = (int) round($feePg * (1 + $ppn / 100));
        }

        $withdrawable = max(0, $amount - $feePg - $feeSysdev);

        return [
            'fee_pg'      => $feePg,
            'fee_sysdev'  => $feeSysdev,
            'withdrawable'=> $withdrawable,
        ];
    }

    /**
     * Hitung fee Xendit berdasarkan metode pembayaran.
     * Referensi: https://docs.xendit.co/fees
     */
    private function calculateXenditFee(string $paymentMethod, int $amount, ?PaymentGatewayFee $feeRule): int
    {
        $method = strtoupper($paymentMethod);

        return match(true) {
            // QR Code / QRIS: flat fee dari DB atau default 0.7%
            str_contains($method, 'QR') => $feeRule?->qr_fee > 0
                ? (int) round($amount * ($feeRule->qr_fee / 100))
                : (int) round($amount * 0.007),

            // Virtual Account / Bank Transfer: flat fee dari DB atau default Rp 4.000
            str_contains($method, 'VIRTUAL_ACCOUNT'),
            str_contains($method, 'BANK_TRANSFER') => $feeRule?->va_fee > 0
                ? (int) $feeRule->va_fee
                : 4000,

            // Retail outlet (Alfamart, dll): flat fee dari DB atau default Rp 5.000
            str_contains($method, 'RETAIL_OUTLET'),
            str_contains($method, 'OUTLET') => $feeRule?->outlet_fee > 0
                ? (int) $feeRule->outlet_fee
                : 5000,

            // Default fallback
            default => 0,
        };
    }
}
