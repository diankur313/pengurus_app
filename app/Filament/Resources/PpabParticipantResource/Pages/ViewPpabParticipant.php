<?php

namespace App\Filament\Resources\PpabParticipantResource\Pages;

use App\Filament\Resources\PpabParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPpabParticipant extends ViewRecord
{
    protected static string $resource = PpabParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
