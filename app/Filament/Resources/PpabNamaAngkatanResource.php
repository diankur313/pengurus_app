<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabNamaAngkatanResource\Pages;
use App\Models\PpabNamaAngkatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpabNamaAngkatanResource extends Resource
{
    protected static ?string $model = PpabNamaAngkatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Daftar Angkatan';
    protected static ?string $modelLabel = 'Angkatan PPAB';
    protected static ?string $pluralModelLabel = 'Daftar Angkatan PPAB';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'daftar-angkatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_angkatan')
                    ->label('Nama Angkatan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_angkatan')
                    ->label('Nama Angkatan')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ManagePpabNamaAngkatans::route('/'),
        ];
    }
}
