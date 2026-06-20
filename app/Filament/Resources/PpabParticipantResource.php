<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabParticipantResource\Pages;
use App\Models\PpabParticipant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class PpabParticipantResource extends Resource
{
    protected static ?string $model = PpabParticipant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Peserta';
    protected static ?string $modelLabel = 'Peserta PPAB';
    protected static ?string $pluralModelLabel = 'Peserta PPAB';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Tambahkan field form di sini
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('stage')
                    ->label('Payment Status')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid_payment' => 'success',
                        'pending_payment' => 'warning',
                        'expired_payment' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPpabParticipants::route('/'),
            'create' => Pages\CreatePpabParticipant::route('/create'),
            'edit' => Pages\EditPpabParticipant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Selalu batasi hanya melihat peserta untuk angkatan = 1
        $query->where('angkatan', '1');

        return $query;
    }
}
