<?php

namespace App\Filament\Resources\BotKnowledgeBaseResource\Pages;

use App\Filament\Resources\BotKnowledgeBaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBotKnowledgeBases extends ManageRecords
{
    protected static string $resource = BotKnowledgeBaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
