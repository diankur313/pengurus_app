<?php

namespace App\Filament\Resources\PpabRegistrationResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PpabRegistration;
use App\Models\PpabPayment;
use App\Models\PpabParticipant;

class PpabRegistrationStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Mendapatkan session terbaru (bisa disesuaikan jika ingin per event spesifik)
        $event = PpabRegistration::latest()->first();

        if (! $event) {
            return [];
        }

        $uuid = $event->uuid ?? null;

        // Quotas — baca dari kolom _original (immutable, di-set admin)
        // Fallback ke kolom live jika _original belum ada (sebelum migrasi)
        $sii_quota_full = $event->sii_quota_full_original ?? $event->sii_quota_full ?? 0;
        $sii_quota_dp = $event->sii_quota_dp_original ?? $event->sii_quota_dp ?? 0;
        $sii_quota_early_bird = $event->sii_quota_early_bird_original ?? $event->sii_quota_early_bird ?? 0;

        $bsq_quota_full = $event->bsq_quota_full_original ?? $event->bsq_quota_full ?? 0;
        $bsq_quota_dp = $event->bsq_quota_dp_original ?? $event->bsq_quota_dp ?? 0;
        $bsq_quota_early_bird = $event->bsq_quota_early_bird_original ?? $event->bsq_quota_early_bird ?? 0;

        $sii_bsq_quota_full = $event->sii_bsq_quota_full_original ?? $event->sii_bsq_quota_full ?? 0;
        $sii_bsq_quota_dp = $event->sii_bsq_quota_dp_original ?? $event->sii_bsq_quota_dp ?? 0;
        $sii_bsq_quota_early_bird = $event->sii_bsq_quota_early_bird_original ?? $event->sii_bsq_quota_early_bird ?? 0;

        // Total Ticket
        $ticket_total = $sii_quota_full + $sii_quota_dp + $sii_quota_early_bird +
                        $bsq_quota_full + $bsq_quota_dp + $bsq_quota_early_bird +
                        $sii_bsq_quota_full + $sii_bsq_quota_dp + $sii_bsq_quota_early_bird;


        // Down Payment
        $dp_quota = $sii_quota_dp + $bsq_quota_dp + $sii_bsq_quota_dp;
        $dp_paid = PpabPayment::where('id_session', $uuid)
            ->where('payment_type', 'dp')
            ->where('status', 'PAID')
            ->count();

        // Full Payment
        $full_quota = $sii_quota_full + $bsq_quota_full + $sii_bsq_quota_full;
        $full_paid = PpabPayment::where('id_session', $uuid)
            ->where('payment_type', 'full')
            ->where('status', 'PAID')
            ->where(function($q) {
                $q->where('early_bird', 0)->orWhereNull('early_bird');
            })
            ->count();

        // Early Bird
        $early_bird_quota = $sii_quota_early_bird + $bsq_quota_early_bird + $sii_bsq_quota_early_bird;
        $early_bird_paid = PpabPayment::where('id_session', $uuid)
            ->where('early_bird', 1)
            ->where('status', 'PAID')
            ->count();

        // Pending Payment
        $hold = PpabPayment::where('id_session', $uuid)
            ->where('status', 'PENDING')
            ->count();

        $stats = [];

        if ($ticket_total > 0) {
            $stats[] = Stat::make('Total Ticket', $ticket_total)
                ->description('Seluruh jenis tiket')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart([2, 5, 8, 4, 12, 15, $ticket_total])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl',
                ]);
        }

        if ($dp_quota > 0 || $dp_paid > 0) {
            $stats[] = Stat::make('Down Payment', "{$dp_paid} / {$dp_quota}")
                ->description('Peserta bayar DP')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([1, 2, 4, 3, 5, 6, $dp_paid])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-primary-500 rounded-2xl',
                ]);
        }

        if ($full_quota > 0 || $full_paid > 0) {
            $stats[] = Stat::make('Full Payment', "{$full_paid} / {$full_quota}")
                ->description('Peserta bayar Full')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([1, 4, 3, 7, 5, 8, $full_paid])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]);
        }

        if ($early_bird_quota > 0 || $early_bird_paid > 0) {
            $stats[] = Stat::make('Early Bird', "{$early_bird_paid} / {$early_bird_quota}")
                ->description('Promo Early Bird')
                ->descriptionIcon('heroicon-m-star')
                ->chart([0, 1, 2, 1, 3, 2, $early_bird_paid])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-emerald-500 rounded-2xl',
                ]);
        }

        $stats[] = Stat::make('Pending Payment', $hold)
            ->description('Menunggu pembayaran')
            ->descriptionIcon('heroicon-m-clock')
            ->chart([5, 3, 6, 2, 7, 4, $hold])
            ->color('danger')
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-danger-500 rounded-2xl',
            ]);

        return $stats;
    }
}
