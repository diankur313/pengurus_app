<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Quiz';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Quiz';
    protected static ?string $pluralModelLabel = 'Quiz';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Quiz')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Quiz')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi (opsional)')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Durasi (MM:DD)')
                            ->required()
                            ->placeholder('15:00')
                            ->helperText('Masukkan durasi dalam format Menit:Detik (contoh: 15:00 untuk 15 menit)'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Aktifkan agar quiz bisa dikerjakan peserta'),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Soal')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->label('')
                            ->relationship()
                            ->reorderable('order')
                            ->orderColumn('order')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => 
                                filled($state['question_text'] ?? null) 
                                    ? \Illuminate\Support\Str::limit(strip_tags($state['question_text']), 60) 
                                    : 'Soal Baru'
                            )
                            ->addActionLabel('+ Tambah Soal')
                            ->schema([
                                Forms\Components\Textarea::make('question_text')
                                    ->label('Pertanyaan')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Radio::make('type')
                                    ->label('Tipe Soal')
                                    ->options([
                                        'multiple_choice' => 'Pilihan Ganda',
                                        'essay' => 'Essay',
                                    ])
                                    ->required()
                                    ->default('multiple_choice')
                                    ->live()
                                    ->inline(),

                                Forms\Components\Repeater::make('options')
                                    ->label('Pilihan Jawaban')
                                    ->relationship()
                                    ->reorderable('order')
                                    ->orderColumn('order')
                                    ->addActionLabel('+ Tambah Pilihan')
                                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'multiple_choice')
                                    ->minItems(2)
                                    ->defaultItems(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('option_text')
                                            ->label('Teks Pilihan')
                                            ->required()
                                            ->columnSpan(3),

                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('Jawaban Benar')
                                            ->default(false)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('duration')
                    ->label('Durasi (MM:DD)')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat oleh')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Draft')
                    ->placeholder('Semua'),
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
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
