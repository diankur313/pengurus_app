<?php

namespace App\Filament\Resources\PpabRegistrationResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PpabRegistration;
use App\Models\PpabPayment;

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

        // Total Ticket
        $ticket_total = ($event->quota_full ?? 0) + ($event->quota_dp ?? 0) + ($event->quota_early_bird ?? 0);

        // Hold / Pending
        $hold = PpabPayment::where('status', 'PENDING')->count();

        // Down Payment
        $dp_quota = $event->quota_dp ?? 0;
        $dp_paid = PpabPayment::where('id_session', $uuid)
            ->where('payment_type', 'dp')
            ->where('status', 'PAID')
            ->count();

        // Full Payment
        $full_quota = $event->quota_full ?? 0;
        $full_paid = PpabPayment::where('id_session', $uuid)
            ->where('payment_type', 'full')
            ->where('status', 'PAID')
            ->where('early_bird', 0)
            ->count();

        // Early Bird
        $early_bird_quota = $event->quota_early_bird ?? 0;
        $early_bird_paid = PpabPayment::where('id_session', $uuid)
            ->where('early_bird', 1)
            ->where('status', 'PAID')
            ->count();

        return [
            Stat::make('Total Ticket', $ticket_total)
                ->description('Seluruh jenis tiket')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart([2, 5, 8, 4, 12, 15, $ticket_total])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl',
                ]),
            
            Stat::make('Down Payment', "{$dp_paid} / {$dp_quota}")
                ->description('Peserta bayar DP')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([1, 2, 4, 3, 5, 6, $dp_paid])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-primary-500 rounded-2xl',
                ]),

            Stat::make('Full Payment', "{$full_paid} / {$full_quota}")
                ->description('Peserta bayar Full')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([1, 4, 3, 7, 5, 8, $full_paid])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]),

            Stat::make('Pending Payment', $hold)
                ->description('Menunggu pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([5, 3, 6, 2, 7, 4, $hold])
                ->color('danger')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-danger-500 rounded-2xl',
                ]),

            Stat::make('Early Bird', "{$early_bird_paid} / {$early_bird_quota}")
                ->description('Promo Early Bird')
                ->descriptionIcon('heroicon-m-star')
                ->chart([0, 1, 2, 1, 3, 2, $early_bird_paid])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-emerald-500 rounded-2xl',
                ]),
        ];
    }
}
