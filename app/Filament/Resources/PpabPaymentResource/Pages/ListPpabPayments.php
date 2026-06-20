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
}
