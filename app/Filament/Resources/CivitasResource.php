<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CivitasResource\Pages;
use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class CivitasResource extends Resource
{
    protected static ?string $model = CivitasPendidikan::class;

    protected static ?string $slug = 'civitas';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Daftar Civitas';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Daftar Civitas';
    protected static ?string $pluralModelLabel = 'Daftar Civitas';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_civitas');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_civitas');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update_civitas');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_civitas');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                $q->where('source_type', 'table_member_lama')
                  ->orWhere(function ($q2) {
                      $q2->where('source_type', 'table_ppab_baru')
                         ->whereIn('source_id', MemberPpab::where('stage', 'paid_payment')->pluck('id_member'));
                  });
            }))
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . ((filled($record->photo) && $record->photo !== 'avatar.png') ? url('/profile-picture/' . $record->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&color=FFFFFF&background=09090b') . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png'
                        ? url('/profile-picture/' . $record->photo)
                        : null
                    )
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&color=FFFFFF&background=09090b'),
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
                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level_angkatan')
                    ->label('Level')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCivitas::route('/'),
        ];
    }
}
