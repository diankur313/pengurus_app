<?php

namespace App\Filament\Resources\PpabAttendanceResource\Pages;

use App\Filament\Resources\PpabAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePpabAttendances extends ManageRecords
{
    protected static string $resource = PpabAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada tombol Create — scan dilakukan via API mobile
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
