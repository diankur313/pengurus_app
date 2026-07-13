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
        $angkatan = data_get($this->tableFilters, 'angkatan.value');
        $this->dispatch('ppab-payment-filter-changed', angkatan: $angkatan);
    }

    public function resetTableFiltersForm(): void
    {
        parent::resetTableFiltersForm();
        $this->dispatch('ppab-payment-filter-changed', angkatan: null);
    }

    public function removeTableFilters(): void
    {
        parent::removeTableFilters();
        $this->dispatch('ppab-payment-filter-changed', angkatan: null);
    }

    public function removeTableFilter(string $filterName, ?string $field = null, bool $isRemovingAllFilters = false): void
    {
        parent::removeTableFilter($filterName, $field, $isRemovingAllFilters);
        $angkatan = data_get($this->tableFilters, 'angkatan.value');
        $this->dispatch('ppab-payment-filter-changed', angkatan: $angkatan);
    }
}
