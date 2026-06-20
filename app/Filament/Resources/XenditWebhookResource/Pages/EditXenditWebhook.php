<?php

namespace App\Filament\Resources\XenditWebhookResource\Pages;

use App\Filament\Resources\XenditWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditXenditWebhook extends EditRecord
{
    protected static string $resource = XenditWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
