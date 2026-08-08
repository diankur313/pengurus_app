<?php

namespace App\Filament\Resources\CivitasResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CivitasPendidikan;
use App\Filament\Resources\CivitasResource;

class CivitasStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $activeFilter = request()->query('paketFilter');

        // Base query: same restriction as the table below (source_type + paid only)
        $base = CivitasResource::scopeCivitasTable(CivitasPendidikan::query());

        // Counts follow the table data: same source filter + paket filter
        $total = (clone $base)->count();

        $sii_count = (clone $base)
            ->where('paket', 'like', '%sii%')
            ->where('paket', 'not like', '%bsq%')
            ->count();

        $bsq_count = (clone $base)
            ->where('paket', 'like', '%bsq%')
            ->where('paket', 'not like', '%sii%')
            ->count();

        $siibsq_count = (clone $base)
            ->where('paket', 'like', '%sii%')
            ->where('paket', 'like', '%bsq%')
            ->count();

        $stats = [];

        // Stat cards (Total, SII, BSQ, SII + BSQ)
        // 1. Total
        $isActiveAll = !$activeFilter;
        $stats[] = Stat::make('Total Civitas', (string) $total)
            ->description($isActiveAll ? ' Semua Civitas' : 'Total semua civitas')
            ->descriptionIcon('heroicon-m-users')
            ->chart([1, 2, 3, 4, 5, 6, $total])
            ->color('success')
            ->url(route('filament.admin.resources.civitas.index'))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl cursor-pointer' . ($isActiveAll ? ' ring-4 ring-success-500 scale-105' : ''),
            ]);

        // 2. SII
        $isActiveSii = $activeFilter === 'sii';
        $stats[] = Stat::make('SII', (string) $sii_count)
            ->description($isActiveSii ? ' Civitas Paket SII' : 'Civitas Paket SII')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([1, 3, 2, 5, 4, 7, $sii_count])
            ->color('primary')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'sii']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-primary-500 rounded-2xl cursor-pointer' . ($isActiveSii ? ' ring-4 ring-primary-500 scale-105' : ''),
            ]);

        // 3. BSQ
        $isActiveBsq = $activeFilter === 'bsq';
        $stats[] = Stat::make('BSQ', (string) $bsq_count)
            ->description($isActiveBsq ? ' Civitas Paket BSQ' : 'Civitas Paket BSQ')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([2, 1, 4, 3, 6, 5, $bsq_count])
            ->color('info')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'bsq']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl cursor-pointer' . ($isActiveBsq ? ' ring-4 ring-info-500 scale-105' : ''),
            ]);

        // 4. SII + BSQ
        $isActiveSiiBsq = $activeFilter === 'sii_bsq';
        $stats[] = Stat::make('SII + BSQ', (string) $siibsq_count)
            ->description($isActiveSiiBsq ? ' Civitas Paket SII + BSQ' : 'Civitas Paket SII + BSQ')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->chart([0, 2, 1, 4, 3, 6, $siibsq_count])
            ->color('warning')
            ->url(route('filament.admin.resources.civitas.index', ['paketFilter' => 'sii_bsq']))
            ->extraAttributes([
                'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl cursor-pointer' . ($isActiveSiiBsq ? ' ring-4 ring-warning-500 scale-105' : ''),
            ]);

        return $stats;
    }
}