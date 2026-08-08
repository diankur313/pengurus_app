<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use App\Models\MemberPpab;
use App\Models\MemberLama;
use App\Models\CivitasPendidikan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Absensi';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Absensi';
    protected static ?int $navigationSort = 4;

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
            ->columns([
                ImageColumn::make('civitas.photo')
                    ->label('Photo')
                    ->circular()
                    ->size(60)
                    ->getStateUsing(fn ($record) => $record->civitas && filled($record->civitas->photo) && $record->civitas->photo !== 'avatar.png'
                        ? profilePhotoUrl($record->civitas->photo, $record->civitas->name)
                        : null
                    )
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->civitas->name ?? null)),
                
                TextColumn::make('civitas.name')
                    ->label('Nama')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('civitas', function ($q) use ($search) {
                            $q->where(function ($qSub) use ($search) {
                                $qSub->where(function ($q1) use ($search) {
                                    $q1->where('source_type', 'table_ppab_baru')
                                       ->whereIn('source_id', MemberPpab::where('name', 'like', "%{$search}%")->pluck('id_member'));
                                })->orWhere(function ($q2) use ($search) {
                                    $q2->where('source_type', 'table_member_lama')
                                       ->whereIn('source_id', MemberLama::where('member_name', 'like', "%{$search}%")->pluck('id'));
                                });
                            });
                        });
                    })
                    ->sortable(),

                TextColumn::make('civitas.angkatan')
                    ->label('Angkatan')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('civitas', function ($q) use ($search) {
                            $q->where(function ($qSub) use ($search) {
                                $qSub->where(function ($q1) use ($search) {
                                    $q1->where('source_type', 'table_ppab_baru')
                                       ->whereIn('source_id', MemberPpab::where('nama_angkatan', 'like', "%{$search}%")->pluck('id_member'));
                                })->orWhere(function ($q2) use ($search) {
                                    $q2->where('source_type', 'table_member_lama')
                                       ->whereIn('source_id', MemberLama::where('member_nama_angkatan', 'like', "%{$search}%")->pluck('id'));
                                });
                            });
                        });
                    })
                    ->sortable(),

                TextColumn::make('civitas.level_angkatan')
                    ->label('Level')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schedule.title')
                    ->label('Sesi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scannedBy.name')
                    ->label('Verifikator')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('angkatan')
                    ->label('Angkatan')
                    ->options(function () {
                        $civitasUuids = Attendance::pluck('civitas_id')->filter()->unique()->toArray();
                        $civitas = CivitasPendidikan::whereIn('uuid', $civitasUuids)->get();
                        
                        return $civitas->map(fn ($c) => $c->angkatan)
                            ->filter()
                            ->unique()
                            ->mapWithKeys(fn ($a) => [$a => $a])
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        $val = $data['value'];
                        return $query->whereHas('civitas', function ($q) use ($val) {
                            $q->where(function ($qSub) use ($val) {
                                $qSub->where(function ($q1) use ($val) {
                                    $q1->where('source_type', 'table_ppab_baru')
                                       ->whereIn('source_id', MemberPpab::where('nama_angkatan', $val)->pluck('id_member'));
                                })->orWhere(function ($q2) use ($val) {
                                    $q2->where('source_type', 'table_member_lama')
                                       ->whereIn('source_id', MemberLama::where('member_nama_angkatan', $val)->pluck('id'));
                                });
                            });
                        });
                    }),

                SelectFilter::make('level_angkatan')
                    ->label('Level')
                    ->options(function () {
                        $civitasUuids = Attendance::pluck('civitas_id')->filter()->unique()->toArray();
                        return CivitasPendidikan::whereIn('uuid', $civitasUuids)
                            ->whereNotNull('level_angkatan')
                            ->pluck('level_angkatan', 'level_angkatan')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('civitas', fn ($q) => $q->where('level_angkatan', $data['value']));
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->extraAttributes([
                                'onkeydown' => 'return false;',
                                'style' => 'caret-color: transparent;',
                            ]),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->extraAttributes([
                                'onkeydown' => 'return false;',
                                'style' => 'caret-color: transparent;',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }
}
