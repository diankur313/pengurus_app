<?php

namespace App\Filament\Resources\CivitasResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;
use App\Models\PpabRegistration;

class CivitasStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $activeFilter = request()->query('paketFilter');

        // base query for active Civitas who are PPAB baru
        $civitasPpabIds = CivitasPendidikan::where('source_type', 'table_ppab_baru')
            ->pluck('source_id');
            
        $sii_paid = MemberPpab::whereIn('id_member', $civitasPpabIds)
            ->where('stage', 'paid_payment')
            ->where('paket', 'like', '%sii%')
            ->where('paket', 'not like', '%bsq%')
            ->count();

        $bsq_paid = MemberPpab::whereIn('id_member', $civitasPpabIds)
            ->where('stage', 'paid_payment')
            ->where('paket', 'like', '%bsq%')
            ->where('paket', 'not like', '%sii%')
            ->count();

        $siibsq_paid = MemberPpab::whereIn('id_member', $civitasPpabIds)
            ->where('stage', 'paid_payment')
            ->where('paket', 'like', '%sii%')
            ->where('paket', 'like', '%bsq%')
            ->count();

        // Get quota from latest PpabRegistration
        $event = PpabRegistration::latest()->first();
        $sii_quota   = 0;
        $bsq_quota   = 0;
        $siibsq_quota = 0;

        if ($event) {
            $sii_quota = ($event->sii_quota_full_original   ?? $event->sii_quota_full   ?? 0)
                       + ($event->sii_quota_dp_original     ?? $event->sii_quota_dp     ?? 0)
                       + ($event->sii_quota_early_bird_original ?? $event->sii_quota_early_bird ?? 0);

            $bsq_quota = ($event->bsq_quota_full_original   ?? $event->bsq_quota_full   ?? 0)
                       + ($event->bsq_quota_dp_original     ?? $event->bsq_quota_dp     ?? 0)
                       + ($event->bsq_quota_early_bird_original ?? $event->bsq_quota_early_bird ?? 0);

            $siibsq_quota = ($event->sii_bsq_quota_full_original   ?? $event->sii_bsq_quota_full   ?? 0)
                          + ($event->sii_bsq_quota_dp_original     ?? $event->sii_bsq_quota_dp     ?? 0)
                          + ($event->sii_bsq_quota_early_bird_original ?? $event->sii_bsq_quota_early_bird ?? 0);
        }

        $stats = [];

        // Stat cards (SII, BSQ, SII + BSQ)
        // 1. SII
        $isActiveSii = $activeFilter === 'sii';
        $stats[] = Stat::make('SII', (string) $sii_paid)
            ->description($isActiveSii ? '✓ Filter Aktif - Peserta Paket SII' : 'Peserta Paket SII')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([1, 3, 2, 5, 4, 7, $sii_paid])
            ->color('primary')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'sii']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-primary-500 rounded-2xl cursor-pointer' . ($isActiveSii ? ' ring-4 ring-primary-500 scale-105' : ''),
            ]);

        // 2. BSQ
        $isActiveBsq = $activeFilter === 'bsq';
        $stats[] = Stat::make('BSQ', (string) $bsq_paid)
            ->description($isActiveBsq ? '✓ Filter Aktif - Peserta Paket BSQ' : 'Peserta Paket BSQ')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([2, 1, 4, 3, 6, 5, $bsq_paid])
            ->color('info')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'bsq']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl cursor-pointer' . ($isActiveBsq ? ' ring-4 ring-info-500 scale-105' : ''),
            ]);

        // 3. SII + BSQ
        $isActiveSiiBsq = $activeFilter === 'sii_bsq';
        $stats[] = Stat::make('SII + BSQ', (string) $siibsq_paid)
            ->description($isActiveSiiBsq ? '✓ Filter Aktif - Peserta Paket SII + BSQ' : 'Peserta Paket SII + BSQ')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([0, 2, 1, 4, 3, 6, $siibsq_paid])
            ->color('warning')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'sii_bsq']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl cursor-pointer' . ($isActiveSiiBsq ? ' ring-4 ring-warning-500 scale-105' : ''),
            ]);

        return $stats;
    }
}