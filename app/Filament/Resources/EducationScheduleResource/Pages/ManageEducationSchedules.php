<?php

namespace App\Filament\Resources\EducationScheduleResource\Pages;

use App\Filament\Resources\EducationScheduleResource;
use App\Filament\Widgets\EducationScheduleCalendarWidget;
use Filament\Resources\Pages\ManageRecords;

class ManageEducationSchedules extends ManageRecords
{
    protected static string $resource = EducationScheduleResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            EducationScheduleCalendarWidget::class,
        ];
    }

    /**
     * Sembunyikan tabel bawaan agar hanya muncul kalender
     */
    public function getHeaderActions(): array
    {
        return [];
    }

    // Override view untuk menyembunyikan tabel jika diperlukan, 
    // namun biasanya meletakkan widget di header sudah cukup dominan.
}
