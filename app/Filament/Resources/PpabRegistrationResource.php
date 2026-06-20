<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabRegistrationResource\Pages;
use App\Models\PpabRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpabRegistrationResource extends Resource
{
    protected static ?string $model = PpabRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Pendaftaran';
    protected static ?string $modelLabel = 'Pendaftaran PPAB';
    protected static ?string $pluralModelLabel = 'Pendaftaran PPAB';
    protected static ?int $navigationSort = 1;

    private static function getPackageSection(string $title, string $prefix): Forms\Components\Section
    {
        return Forms\Components\Section::make($title)
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make($prefix . 'quota_full')
                        ->label('Full Payment Quota')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make($prefix . 'price_full')
                        ->label('Price')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),

                    Forms\Components\TextInput::make($prefix . 'quota_dp')
                        ->label('Down Payment Quota')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make($prefix . 'price_dp')
                        ->label('Price')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),

                    Forms\Components\TextInput::make($prefix . 'quota_early_bird')
                        ->label('Early Bird Quota')
                        ->numeric(),
                    Forms\Components\TextInput::make($prefix . 'price_early_bird')
                        ->label('Price')
                        ->numeric()
                        ->prefix('Rp'),

                    Forms\Components\TextInput::make($prefix . 'bundling_2')
                        ->label('Harga Bundling 2 Members')
                        ->helperText('Isi dengan total harga. Mengambil kuota dari Full Payment.')
                        ->prefix('Rp')
                        ->numeric(),
                    Forms\Components\TextInput::make($prefix . 'bundling_3')
                        ->label('Harga Bundling 3 Members')
                        ->helperText('Isi dengan total harga. Mengambil kuota dari Full Payment.')
                        ->prefix('Rp')
                        ->numeric(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('General Info')
                ->schema([
                    Forms\Components\Grid::make(5)->schema([
                        Forms\Components\DateTimePicker::make('session_date_start')
                            ->label('Start')
                            ->required(),
                        Forms\Components\DateTimePicker::make('session_date_end')
                            ->label('End')
                            ->required(),
                        Forms\Components\TextInput::make('cp')
                            ->label('Whatsapp CP')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('group_link_ikhwan')
                            ->label('WA Group Ikhwan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('group_link_akhwat')
                            ->label('WA Group Akhwat')
                            ->maxLength(255),
                    ]),
                ]),

            self::getPackageSection('SII', 'sii_'),
            self::getPackageSection('BSQ', 'bsq_'),
            self::getPackageSection('SII + BSQ', 'sii_bsq_'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status_agenda')
                    ->label('Status Agenda')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $now = now();
                        $start = \Carbon\Carbon::parse($record->session_date_start);
                        $end = \Carbon\Carbon::parse($record->session_date_end);
                        
                        if ($now->lt($start)) {
                            return 'Akan Berlangsung';
                        } elseif ($now->between($start, $end)) {
                            return 'Sedang Berlangsung';
                        } else {
                            return 'Sudah Selesai';
                        }
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Akan Berlangsung' => 'warning',
                            'Sedang Berlangsung' => 'success',
                            'Sudah Selesai' => 'gray',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('session_date_start')
                    ->label('Start')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_date_end')
                    ->label('End')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sii_price_full')
                    ->label('SII Full')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bsq_price_full')
                    ->label('BSQ Full')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sii_bsq_price_full')
                    ->label('SII+BSQ Full')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cp')
                    ->label('CP')
                    ->searchable(),
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
            'index' => Pages\ListPpabRegistrations::route('/'),
            'create' => Pages\CreatePpabRegistration::route('/create'),
            'edit' => Pages\EditPpabRegistration::route('/{record}/edit'),
        ];
    }
}
