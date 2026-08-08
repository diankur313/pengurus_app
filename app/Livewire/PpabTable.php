<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Models\MemberPpab;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class PpabTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MemberPpab::query()->whereNotNull('id_member')->where('id_member', '!=', ''))
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png'
                        ? profilePhotoUrl($record->photo, $record->name)
                        : null
                    )
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->name)),
                TextColumn::make('id_member')
                    ->label('Member ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('No. HP')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        $clean = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($clean, '0')) {
                            $clean = '62' . substr($clean, 1);
                        } elseif (str_starts_with($clean, '8')) {
                            $clean = '62' . $clean;
                        }

                        $waIcon = '<svg style="width:18px;height:18px;fill:#25D366;flex-shrink:0;transition:transform .15s" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" onmouseover="this.style.transform=\'scale(1.2)\'" onmouseout="this.style.transform=\'scale(1)\'"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.457L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.97C16.579 1.968 14.1 1.94 12.001 1.94c-5.439 0-9.867 4.372-9.87 9.802 0 1.764.484 3.486 1.4 5.013l-.974 3.564 3.69-.965zm12.333-6.223c-.303-.151-1.793-.88-2.057-.976-.264-.097-.456-.145-.648.145-.192.29-.744.976-.91 1.17-.168.192-.335.215-.638.064-3.155-1.576-4.526-2.584-5.385-4.061-.264-.456-.03-.703.197-.93.205-.205.456-.53.684-.795.228-.264.303-.456.456-.759.151-.303.075-.568-.038-.795-.113-.227-.91-2.203-1.248-3.017-.33-.795-.664-.687-.91-.699l-.776-.014c-.264 0-.696.099-.958.384-.264.288-1.008.983-1.008 2.398 0 1.416 1.032 2.784 1.176 2.978.145.193 2.033 3.094 4.925 4.339.687.296 1.224.473 1.643.606.69.219 1.319.188 1.815.114.553-.083 1.793-.728 2.048-1.433.255-.705.255-1.309.18-1.433-.075-.124-.264-.197-.568-.347z"/></svg>';

                        return '<span style="display:inline-flex;align-items:center;gap:6px">'
                            . e($state)
                            . '<a href="https://wa.me/' . $clean . '" target="_blank" title="Chat WhatsApp" style="display:inline-flex;align-items:center;line-height:1">' . $waIcon . '</a>'
                            . '</span>';
                    })
                    ->searchable(),
            ]);
    }

    public function render()
    {
        return view('livewire.ppab-table');
    }
}
