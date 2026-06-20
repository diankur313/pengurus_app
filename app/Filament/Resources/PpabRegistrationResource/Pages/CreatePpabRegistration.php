<?php

namespace App\Filament\Resources\PpabRegistrationResource\Pages;

use App\Filament\Resources\PpabRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePpabRegistration extends CreateRecord
{
    protected static string $resource = PpabRegistrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
