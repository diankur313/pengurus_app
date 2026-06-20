<?php

namespace App\Filament\Resources\PpabParticipantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PpabParticipant;

class PpabParticipantStatsWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $query = PpabParticipant::query();

        // Selalu batasi hanya angkatan = 1
        $query->where('angkatan', '1');

        // --- ON PROCESS ---
        $all_process = (clone $query)->whereNull('id_member')->whereNotNull('email_verified_at')->count();
        $male_process = (clone $query)->whereNull('id_member')->whereNotNull('email_verified_at')->where('gender', 'pria')->count();
        $female_process = (clone $query)->whereNull('id_member')->whereNotNull('email_verified_at')->where('gender', 'wanita')->count();

        // --- REGISTERED ---
        $all_registered = (clone $query)->whereNotNull('id_member')->count();
        $male_registered = (clone $query)->whereNotNull('id_member')->where('gender', 'pria')->count();
        $female_registered = (clone $query)->whereNotNull('id_member')->where('gender', 'wanita')->count();

        return [
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
    }
}
