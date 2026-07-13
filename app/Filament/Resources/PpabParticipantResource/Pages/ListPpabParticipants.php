<?php

namespace App\Filament\Resources\PpabParticipantResource\Pages;

use App\Filament\Resources\PpabParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPpabParticipants extends ListRecords
{
    protected static string $resource = PpabParticipantResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];
        
        // Add "Clear Filter" button if a filter is active
        $paketFilter = request()->query('paketFilter');
        if ($paketFilter) {
            $actions[] = Actions\Action::make('clearFilter')
                ->label('Clear Filter')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->url(route('filament.admin.resources.ppab-participants.index'))
                ->outlined();
        }
        
        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PpabParticipantResource\Widgets\PpabParticipantStatsWidget::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Check if there's a paket filter in the URL
        $paketFilter = request()->query('paketFilter');
        
        if ($paketFilter) {
            switch ($paketFilter) {
                case 'sii':
                    // Filter for SII only (contains 'sii' but not 'bsq')
                    $query->where('paket', 'like', '%sii%')
                          ->where('paket', 'not like', '%bsq%');
                    break;
                    
                case 'bsq':
                    // Filter for BSQ only (contains 'bsq' but not 'sii')
                    $query->where('paket', 'like', '%bsq%')
                          ->where('paket', 'not like', '%sii%');
                    break;
                    
                case 'sii_bsq':
                    // Filter for SII + BSQ (contains both)
                    $query->where('paket', 'like', '%sii%')
                          ->where('paket', 'like', '%bsq%');
                    break;
            }
        }
        
        return $query;
    }

    public function getTitle(): string
    {
        $paketFilter = request()->query('paketFilter');
        
        if ($paketFilter) {
            $filterName = match($paketFilter) {
                'sii' => 'SII',
                'bsq' => 'BSQ',
                'sii_bsq' => 'SII + BSQ',
                default => ''
            };
            
            return "Peserta PPAB - Paket {$filterName}";
        }
        
        return 'Peserta PPAB';
    }
}
