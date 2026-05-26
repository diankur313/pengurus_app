<?php

namespace App\Filament\Resources\HasilQuizResource\Pages;

use App\Filament\Resources\HasilQuizResource;
use Filament\Resources\Pages\ListRecords;

class ListHasilQuiz extends ListRecords
{
    protected static string $resource = HasilQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
