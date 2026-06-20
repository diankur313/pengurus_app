<?php

namespace App\Filament\Resources\XenditWebhookResource\Pages;

use App\Filament\Resources\XenditWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListXenditWebhooks extends ListRecords
{
    protected static string $resource = XenditWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Aplikasi'),
        ];
    }
}
