<?php

namespace App\Filament\Resources\MutabaahQuranResource\Pages;

use App\Filament\Resources\MutabaahQuranResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMutabaahQurans extends ManageRecords
{
    protected static string $resource = MutabaahQuranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Dinonaktifkan karena data Civitas dibuat dari menu Civitas
        ];
    }

    public function deleteMutabaah($id)
    {
        $mutabaah = \App\Models\MutabaahQuran::find($id);
        if ($mutabaah) {
            $mutabaah->delete();
            
            \Filament\Notifications\Notification::make()
                ->title('Setoran Berhasil Dihapus')
                ->success()
                ->send();
        }
    }
}
