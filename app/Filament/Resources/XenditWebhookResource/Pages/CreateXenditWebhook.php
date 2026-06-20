<?php

namespace App\Filament\Resources\XenditWebhookResource\Pages;

use App\Filament\Resources\XenditWebhookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateXenditWebhook extends CreateRecord
{
    protected static string $resource = XenditWebhookResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
