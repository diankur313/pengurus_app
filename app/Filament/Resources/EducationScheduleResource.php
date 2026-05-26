<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationScheduleResource\Pages;
use App\Models\EducationSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EducationScheduleResource extends Resource
{
    protected static ?string $model = EducationSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jadwal Pembelajaran';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Jadwal Pembelajaran';
    protected static ?string $pluralModelLabel = 'Jadwal Pembelajaran';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'pembelajaran' => 'Pembelajaran',
                        'quiz' => 'Quiz',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('teacher_id')
                    ->label('Ustadz')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === 'pembelajaran')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('quiz_id')
                    ->label('Pilih Quiz')
                    ->options(function (?EducationSchedule $record) {
                        $usedQuizIds = \App\Models\EducationSchedule::query()
                            ->whereNotNull('quiz_id')
                            ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                            ->pluck('quiz_id')
                            ->toArray();

                        return \App\Models\Quiz::where('is_published', true)
                            ->whereNotIn('id', $usedQuizIds)
                            ->pluck('title', 'id');
                    })
                    ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                    ->required(fn (Forms\Get $get) => $get('type') === 'quiz')
                    ->searchable()
                    ->helperText('Hanya quiz yang belum digunakan akan ditampilkan'),

                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),

                Forms\Components\Radio::make('level')
                    ->label('Angkatan')
                    ->options([
                        'dasar' => 'Angkatan Dasar',
                        'lanjutan' => 'Angkatan Lanjutan',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('start_at')
                    ->label('Mulai')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_at')
                    ->label('Selesai')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
                    ->label('Mulai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Ustadz'),
                Tables\Columns\TextColumn::make('level')
                    ->label('Angkatan')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEducationSchedules::route('/'),
        ];
    }
}
