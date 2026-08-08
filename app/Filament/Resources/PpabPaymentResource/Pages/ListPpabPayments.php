<?php

namespace App\Filament\Resources\PpabPaymentResource\Pages;

use App\Filament\Resources\PpabPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPpabPayments extends ListRecords
{
    protected static string $resource = PpabPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('report')
                ->label('Report')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->modalHeading('Pilih Tipe Report')
                ->modalDescription('Silakan pilih jenis report yang ingin Anda generate')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(view('filament.modals.ppab-payment-report'))
                ->visible(fn () => auth()->user()?->can('report_ppab::payment')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PpabPaymentResource\Widgets\PpabPaymentStatsWidget::class,
        ];
    }

    /**
     * Dispatch event to the stats widget whenever table filters change.
     * Livewire v3 fires this hook when any key inside $tableFilters is updated.
     */
    public function updatedTableFilters(): void
    {
        $session = data_get($this->tableFilters, 'session.value');
        $this->dispatch('ppab-payment-filter-changed', session: $session);
    }

    public function resetTableFiltersForm(): void
    {
        parent::resetTableFiltersForm();
        $this->dispatch('ppab-payment-filter-changed', session: null);
    }

    public function removeTableFilters(): void
    {
        parent::removeTableFilters();
        $this->dispatch('ppab-payment-filter-changed', session: null);
    }

    public function removeTableFilter(string $filterName, ?string $field = null, bool $isRemovingAllFilters = false): void
    {
        parent::removeTableFilter($filterName, $field, $isRemovingAllFilters);
        $session = data_get($this->tableFilters, 'session.value');
        $this->dispatch('ppab-payment-filter-changed', session: $session);
    }
}
