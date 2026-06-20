<?php

namespace App\Filament\Resources\PpabParticipantResource\Pages;

use App\Filament\Resources\PpabParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPpabParticipants extends ListRecords
{
    protected static string $resource = PpabParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PpabParticipantResource\Widgets\PpabParticipantStatsWidget::class,
        ];
    }
}
