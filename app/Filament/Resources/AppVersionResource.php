<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVersionResource\Pages;
use App\Models\AppVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'App Versions';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'App Version';
    protected static ?string $pluralModelLabel = 'App Versions';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform')
                    ->options([
                        'android' => 'Android',
                        'ios' => 'iOS',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('version_name')
                    ->label('Version Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Contoh: 2.0.0'),

                Forms\Components\TextInput::make('version_code')
                    ->label('Version Code')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required()
                    ->helperText('Harus sama dengan versionCode di build.gradle app'),

                Forms\Components\Toggle::make('is_mandatory')
                    ->label('Jadikan versi paten (wajib)')
                    ->helperText('Kalau aktif, semua user dengan versi di bawah ini akan diblok sampai update.')
                    ->live(),

                Forms\Components\Textarea::make('custom_message')
                    ->label('Pesan yang muncul saat user diblok')
                    ->visible(fn ($get) => $get('is_mandatory'))
                    ->required(fn ($get) => $get('is_mandatory'))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('download_url')
                    ->label('Download URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Link Play Store / App Store'),

                Forms\Components\Textarea::make('changelog')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('released_at')
                    ->native(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'android' => 'success',
                        'ios' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('version_name')
                    ->label('Version Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('version_code')
                    ->label('Version Code')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_mandatory')
                    ->boolean()
                    ->label('Paten'),

                Tables\Columns\TextColumn::make('download_url')
                    ->label('Download URL')
                    ->limit(30)
                    ->url(fn (?AppVersion $record) => $record?->download_url, shouldOpenInNewTab: true)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('released_at')
                    ->label('Released At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'android' => 'Android',
                        'ios' => 'iOS',
                    ]),
                Tables\Filters\TernaryFilter::make('is_mandatory')
                    ->label('Paten'),
            ])
            ->defaultSort('version_code', 'desc')
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
            'index' => Pages\ManageAppVersions::route('/'),
        ];
    }
}
