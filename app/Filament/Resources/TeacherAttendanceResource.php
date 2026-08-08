<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAttendanceResource\Pages;
use App\Filament\Resources\TeacherAttendanceResource\RelationManagers;
use App\Models\TeacherAttendance;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeacherAttendanceResource extends Resource
{
    protected static ?string $model = TeacherAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Absensi Ustadz';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Absensi Ustadz';
    protected static ?string $pluralModelLabel = 'Absensi Ustadz';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Nama Pengajar')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DateTimePicker::make('attendance_date')
                    ->label('Waktu Kedatangan')
                    ->displayFormat('l, d F Y H:i')
                    ->native(false)
                    ->seconds(false)
                    ->required()
                    ->rules([
                        function (Get $get, ?Model $record) {
                            return function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                $teacherId = $get('teacher_id');

                                if (!$teacherId) {
                                    return;
                                }

                                $date = $value instanceof \DateTimeInterface
                                    ? $value->format('Y-m-d')
                                    : date('Y-m-d', strtotime($value));

                                $query = TeacherAttendance::query()
                                    ->where('teacher_id', $teacherId)
                                    ->whereDate('attendance_date', $date);

                                if ($record) {
                                    $query->where('id', '!=', $record->getKey());
                                }

                                if ($query->exists()) {
                                    $fail('Absensi ustadz sudah tercatat pada tanggal tersebut.');
                                }
                            };
                        },
                    ]),

                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Nama Pengajar')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Waktu Kedatangan')
                    ->dateTime('l, d F Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Nama Pengajar')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAttendances::route('/'),
            'create' => Pages\CreateTeacherAttendance::route('/create'),
            'edit' => Pages\EditTeacherAttendance::route('/{record}/edit'),
        ];
    }
}
