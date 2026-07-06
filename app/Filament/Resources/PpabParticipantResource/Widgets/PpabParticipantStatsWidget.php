<?php

namespace App\Filament\Resources\PpabParticipantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PpabParticipant;
use App\Models\PpabRegistration;
use Illuminate\Support\Facades\DB;

class PpabParticipantStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $query = PpabParticipant::query()->where('angkatan', '1');

        // --- ON PROCESS (Belum Bayar) ---
        $onProcessQuery = (clone $query)->where(function ($q) {
            $q->where('stage', '!=', 'paid_payment')->orWhereNull('stage');
        });
        $male_process   = (clone $onProcessQuery)->where('gender', 'pria')->count();
        $female_process = (clone $onProcessQuery)->where('gender', 'wanita')->count();
        $all_process    = $male_process + $female_process;

        // --- REGISTERED (Sudah Bayar) ---
        $registeredQuery = (clone $query)->where('stage', 'paid_payment');
        $male_registered   = (clone $registeredQuery)->where('gender', 'pria')->count();
        $female_registered = (clone $registeredQuery)->where('gender', 'wanita')->count();
        $all_registered    = $male_registered + $female_registered;

        // --- QUOTA per PAKET (dari session terbaru) ---
        $event = PpabRegistration::latest()->first();
        $sii_quota   = 0;
        $bsq_quota   = 0;
        $siibsq_quota = 0;

        if ($event) {
            // SII total quota (full + dp + early bird)
            $sii_quota = ($event->sii_quota_full_original   ?? $event->sii_quota_full   ?? 0)
                       + ($event->sii_quota_dp_original     ?? $event->sii_quota_dp     ?? 0)
                       + ($event->sii_quota_early_bird_original ?? $event->sii_quota_early_bird ?? 0);

            // BSQ total quota
            $bsq_quota = ($event->bsq_quota_full_original   ?? $event->bsq_quota_full   ?? 0)
                       + ($event->bsq_quota_dp_original     ?? $event->bsq_quota_dp     ?? 0)
                       + ($event->bsq_quota_early_bird_original ?? $event->bsq_quota_early_bird ?? 0);

            // SII + BSQ total quota
            $siibsq_quota = ($event->sii_bsq_quota_full_original   ?? $event->sii_bsq_quota_full   ?? 0)
                          + ($event->sii_bsq_quota_dp_original     ?? $event->sii_bsq_quota_dp     ?? 0)
                          + ($event->sii_bsq_quota_early_bird_original ?? $event->sii_bsq_quota_early_bird ?? 0);
        }

        // --- PAID per PAKET (dari ppab_member stage=paid_payment, filter by paket field) ---
        $paidBase = (clone $query)->where('stage', 'paid_payment');

        $sii_paid    = (clone $paidBase)->where('paket', 'like', '%sii%')
                                         ->where('paket', 'not like', '%bsq%')
                                         ->count();
        $bsq_paid    = (clone $paidBase)->where('paket', 'like', '%bsq%')
                                         ->where('paket', 'not like', '%sii%')
                                         ->count();
        $siibsq_paid = (clone $paidBase)->where('paket', 'like', '%sii%')
                                         ->where('paket', 'like', '%bsq%')
                                         ->count();

        $stats = [
            // Row 1: On Process
            Stat::make('Total', $all_process)
                ->description('On Process')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([1, 2, 4, 3, 5, 4, $all_process])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl',
                ]),

            Stat::make('Male', $male_process)
                ->description('On Process')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([0, 1, 3, 2, 4, 3, $male_process])
                ->color('info')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl',
                ]),

            Stat::make('Female', $female_process)
                ->description('On Process')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([1, 3, 2, 4, 3, 5, $female_process])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]),

            // Row 2: Registered
            Stat::make('Total', $all_registered)
                ->description('Registered')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([2, 5, 4, 7, 6, 9, $all_registered])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl',
                ]),

            Stat::make('Male', $male_registered)
                ->description('Registered')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([1, 2, 5, 3, 6, 4, $male_registered])
                ->color('info')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl',
                ]),

            Stat::make('Female', $female_registered)
                ->description('Registered')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([3, 1, 4, 2, 5, 6, $female_registered])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]),
        ];

        // Row 3: Paket — hanya tampil jika quota tersedia
        if ($sii_quota > 0) {
            $stats[] = Stat::make('SII', "{$sii_paid} / {$sii_quota}")
                ->description('Peserta Paket SII')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([1, 3, 2, 5, 4, 7, $sii_paid])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-primary-500 rounded-2xl',
                ]);
        }

        if ($bsq_quota > 0) {
            $stats[] = Stat::make('BSQ', "{$bsq_paid} / {$bsq_quota}")
                ->description('Peserta Paket BSQ')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([2, 1, 4, 3, 6, 5, $bsq_paid])
                ->color('info')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl',
                ]);
        }

        if ($siibsq_quota > 0) {
            $stats[] = Stat::make('SII + BSQ', "{$siibsq_paid} / {$siibsq_quota}")
                ->description('Peserta Paket SII + BSQ')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([0, 2, 1, 4, 3, 6, $siibsq_paid])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]);
        }

        return $stats;
    }
}

