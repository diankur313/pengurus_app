<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotKnowledgeBaseResource\Pages;
use App\Models\BotKnowledgeBase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Intervention\Image\Laravel\Facades\Image;

class BotKnowledgeBaseResource extends Resource
{
    protected static ?string $model = BotKnowledgeBase::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Bot Knowledge Base';
    protected static ?string $navigationGroup = 'PR / Humas';
    protected static ?string $modelLabel = 'Knowledge Base';
    protected static ?string $pluralModelLabel = 'Bot Knowledge Base';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('topik')
                    ->label('Topik')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('deskripsi')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('attachments')
                    ->label('Attachment Gambar')
                    ->multiple()
                    ->image()
                    ->directory('bot-knowledge-base')
                    ->imagePreviewHeight('100')
                    ->panelLayout('grid')
                    ->saveUploadedFileUsing(function ($file) {
                        $image = Image::read($file->getRealPath());
                        $encoded = $image->toWebp(quality: 85);
                        $filename = uniqid('img_', true) . '.webp';
                        $path = 'bot-knowledge-base/' . $filename;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded);
                        return $path;
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('topik')
                    ->label('Topik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->html()
                    ->limit(100),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
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
            'index' => Pages\ManageBotKnowledgeBases::route('/'),
        ];
    }
}
