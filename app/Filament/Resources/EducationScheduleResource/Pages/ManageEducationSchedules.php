<?php

namespace App\Filament\Resources\EducationScheduleResource\Pages;

use App\Filament\Resources\EducationScheduleResource;
use App\Filament\Widgets\EducationScheduleCalendarWidget;
use Filament\Resources\Pages\ManageRecords;

use Livewire\Attributes\On;
use Filament\Notifications\Notification;

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

    #[On('refreshTable')]
    public function refreshTable(): void
    {
        // triggers re-render of component to reload table data
    }

    #[On('notify-copied')]
    public function notifyCopied(): void
    {
        Notification::make()
            ->title('Link Google Meet berhasil dicopy!')
            ->success()
            ->send();
    }
}
