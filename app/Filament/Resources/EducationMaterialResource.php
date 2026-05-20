<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationMaterialResource\Pages;
use App\Models\EducationMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EducationMaterialResource extends Resource
{
    protected static ?string $model = EducationMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Materi';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Materi Pembelajaran';
    protected static ?string $pluralModelLabel = 'Materi Pembelajaran';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('education_schedule_id')
                    ->label('Jadwal Pembelajaran')
                    ->relationship('educationSchedule', 'title')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\EducationSchedule $record) => "{$record->title} - {$record->start_at->format('d M Y')} - " . ($record->teacher ? $record->teacher->name : 'Tanpa Ustadz'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('File Materi')
                    ->directory('materi-pembelajaran')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(25600)
                    ->multiple()
                    ->preserveFilenames()
                    ->reorderable()
                    ->downloadable()
                    ->helperText('Format yang didukung: PDF, PPT, PPTX, DOC, DOCX. Maksimal ukuran 25MB per file. Anda dapat memilih beberapa file sekaligus. Catatan: Jika Anda menghapus/mengganti file di form ini, file lama akan otomatis terhapus.')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('educationSchedule.title')
                    ->label('Materi/Jadwal')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('educationSchedule.start_at')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('educationSchedule.teacher.name')
                    ->label('Ustadz')
                    ->searchable(),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('Jumlah File')
                    ->formatStateUsing(fn ($state) => count((array)$state) . ' File')
                    ->badge()
                    ->color('success'),
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
            'index' => Pages\ManageEducationMaterials::route('/'),
        ];
    }
}
