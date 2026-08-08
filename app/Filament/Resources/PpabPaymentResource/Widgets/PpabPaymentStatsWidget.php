<?php

namespace App\Filament\Resources\PpabPaymentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PpabPayment;
use Livewire\Attributes\On;

class PpabPaymentStatsWidget extends BaseWidget
{
    /**
     * Current session filter value, synced from the table via Livewire event.
     */
    public ?string $sessionFilter = null;

    /**
     * Initialize filter from URL on first (full) page load.
     */
    public function mount(): void
    {
        $this->sessionFilter = request()->query('tableFilters')['session']['value'] ?? null;
    }

    /**
     * Listen for the event dispatched by ListPpabPayments::updatedTableFilters().
     * Updates the reactive property, causing Livewire to re-render with new stats.
     */
    #[On('ppab-payment-filter-changed')]
    public function onFilterChanged(?string $session = null): void
    {
        $this->sessionFilter = $session;
    }

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $sessionFilter = $this->sessionFilter;

        // Only count transactions with status PAID
        $baseQuery = PpabPayment::query()
            ->where('status', 'PAID');

        // Apply session filter if present
        if ($sessionFilter) {
            $baseQuery->where('id_session', $sessionFilter);
        }

        // If not super_admin, limit to angkatan = 1
        if (! auth()->user()->hasRole('super_admin')) {
            $baseQuery->whereHas('member', function ($q) {
                $q->where('angkatan', '1');
            });
        }

        // Calculate totals
        $totalAmount = (clone $baseQuery)->sum('amount') ?? 0;

        $dpAmount   = (clone $baseQuery)->where('payment_type', 'dp')->sum('amount') ?? 0;
        $fullAmount = (clone $baseQuery)->where('payment_type', 'full')->sum('amount') ?? 0;

        // Counts for chart sparklines
        $totalCount = (clone $baseQuery)->count();
        $dpCount    = (clone $baseQuery)->where('payment_type', 'dp')->count();
        $fullCount  = (clone $baseQuery)->where('payment_type', 'full')->count();

        return [
            Stat::make('Total Pembayaran', 'Rp ' . number_format($totalAmount, 0, ',', '.'))
                ->description('Jumlah keseluruhan uang masuk')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([1, 3, 5, 4, 6, 8, max(1, $totalCount)])
                ->color('success')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-success-500 rounded-2xl',
                ]),

            Stat::make('Down Payment (DP)', 'Rp ' . number_format($dpAmount, 0, ',', '.'))
                ->description('Total pembayaran DP')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([0, 2, 3, 2, 4, 5, max(1, $dpCount)])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-warning-500 rounded-2xl',
                ]),

            Stat::make('Full Payment', 'Rp ' . number_format($fullAmount, 0, ',', '.'))
                ->description('Total pembayaran lunas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([1, 2, 4, 3, 5, 6, max(1, $fullCount)])
                ->color('info')
                ->extraAttributes([
                    'class' => 'hover:scale-105 hover:shadow-xl transition-all duration-300 bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-t-4 border-info-500 rounded-2xl',
                ]),
        ];
    }
}