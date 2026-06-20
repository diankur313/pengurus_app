<?php

namespace App\Filament\Resources\PpabRegistrationResource\Pages;

use App\Filament\Resources\PpabRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPpabRegistration extends EditRecord
{
    protected static string $resource = PpabRegistrationResource::class;

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
