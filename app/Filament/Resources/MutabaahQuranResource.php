<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MutabaahQuranResource\Pages;
use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;

class MutabaahQuranResource extends Resource
{
    protected static ?string $model = CivitasPendidikan::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Mutabaah Quran';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Mutabaah Quran';
    protected static ?string $pluralModelLabel = 'Mutabaah Quran';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && method_exists($user, 'can') && $user->can('view_any_mutabaah::quran');
    }

    public static function getEloquentQuery(): Builder
    {
        $bsqMemberIds = MemberPpab::where('paket', 'like', '%bsq%')->pluck('id_member');

        return parent::getEloquentQuery()
            ->where('source_type', 'table_ppab_baru')
            ->whereIn('source_id', $bsqMemberIds);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(60)
                    ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png'
                        ? profilePhotoUrl($record->photo, $record->name)
                        : null
                    )
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->name ?? null)),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('source_type', 'table_ppab_baru')
                              ->whereIn('source_id', MemberPpab::where('name', 'like', "%{$search}%")->pluck('id_member'));
                        });
                    })
                    ->sortable(),

                TextColumn::make('paket')
                    ->label('Paket')
                    ->getStateUsing(fn ($record) => $record->paket ?? '-'),

                TextColumn::make('angkatan_level')
                    ->label('Angkatan / Level')
                    ->getStateUsing(fn ($record) => ($record->angkatan ?? '-') . ' / ' . ucfirst($record->level_angkatan ?? '-')),

                TextColumn::make('terakhir_setor')
                    ->label('Terakhir Setor')
                    ->getStateUsing(fn ($record) => $record->mutabaahQurans()->latest('pertama_setor')->first()?->pertama_setor?->format('d M Y') ?? '-'),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $last = $record->mutabaahQurans()->latest('id')->first();
                        if (!$last) return '-';
                        return "Surah: {$last->from_surah} Ayat: {$last->from_ayat} => Surah: {$last->to_surah} Ayat: {$last->to_ayat}";
                    }),

                TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(function ($record) {
                        $totalHalaman = $record->mutabaahQurans()->sum('total_halaman');
                        $totalJuz = $record->mutabaahQurans()->sum('total_juz');
                        return "{$totalHalaman} Halaman => {$totalJuz} Juz";
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('catat_mutabaah')
                    ->label('Catat Mutabaah')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->form([
                        DatePicker::make('pertama_setor')
                            ->label('Tanggal Setor')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_surah')
                                    ->label('Dari Surah')
                                    ->placeholder('Contoh: Al-Baqarah')
                                    ->required(),

                                TextInput::make('from_ayat')
                                    ->label('Dari Ayat')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('to_surah')
                                    ->label('Sampai Surah')
                                    ->placeholder('Contoh: Al-Baqarah')
                                    ->required(),

                                TextInput::make('to_ayat')
                                    ->label('Sampai Ayat')
                                    ->numeric()
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_halaman')
                                    ->label('Total Halaman')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('total_juz')
                                    ->label('Total Juz')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required(),
                            ]),
                    ])
                    ->action(function ($record, array $data) {
                        $record->mutabaahQurans()->create([
                            'pertama_setor' => $data['pertama_setor'],
                            'from_surah' => $data['from_surah'],
                            'from_ayat' => $data['from_ayat'],
                            'to_surah' => $data['to_surah'],
                            'to_ayat' => $data['to_ayat'],
                            'total_halaman' => $data['total_halaman'],
                            'total_juz' => $data['total_juz'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Mutabaah Berhasil Dicatat')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('riwayat')
                    ->label('Riwayat')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->color('info')
                    ->modalSubmitAction(false)
                    ->modalContent(fn ($record) => view('filament.riwayat-mutabaah', [
                        'record' => $record,
                        'riwayat' => $record->mutabaahQurans()->orderBy('pertama_setor', 'desc')->get(),
                    ])),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMutabaahQurans::route('/'),
        ];
    }
}