<?php

namespace App\Filament\Resources\PpabNamaAngkatanResource\Pages;

use App\Filament\Resources\PpabNamaAngkatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePpabNamaAngkatans extends ManageRecords
{
    protected static string $resource = PpabNamaAngkatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
