<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabAttendanceResource\Pages;
use App\Models\PpabAttendance;
use App\Models\PpabParticipant;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class PpabAttendanceResource extends Resource
{
    protected static ?string $model = PpabParticipant::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Absensi';
    protected static ?string $modelLabel      = 'Absensi PPAB';
    protected static ?string $pluralModelLabel = 'Absensi PPAB';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $slug            = 'ppab-absensi';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                PpabParticipant::query()
                    ->whereNull('nama_angkatan')
                    ->whereHas('paidPayment', function (Builder $q) {
                        $q->whereIn('payment_type', ['full', 'dp']);
                    })
                    ->with(['attendance', 'paidPayment'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('#')
                    ->rowIndex()
                    ->width('50px')
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pria'   => '🧑 Ikhwan',
                        'wanita' => '👩 Akhwat',
                        default  => '-',
                    })
                    ->sortable(),

                TextColumn::make('paidPayment.payment_type')
                    ->label('Tipe Bayar')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'full' => 'Full Payment',
                        'dp'   => 'Down Payment',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Full Payment' => 'success',
                        'Down Payment' => 'warning',
                        default        => 'gray',
                    }),

                TextColumn::make('attendance.created_at')
                    ->label('Waktu Datang')
                    ->getStateUsing(function (PpabParticipant $record): ?string {
                        return $record->attendance?->created_at ?? null;
                    })
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) return '-';
                        try {
                            return Carbon::parse($state)->locale('id')->isoFormat('D MMM YYYY, HH:mm:ss');
                        } catch (\Exception $e) {
                            return $state;
                        }
                    })
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\Action::make('hadir')
                    ->label('Hadir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->disabled(fn (PpabParticipant $record): bool => $record->attendance !== null)
                    ->action(function (PpabParticipant $record): void {
                        // Cek duplikat sekali lagi (race condition guard)
                        $exists = PpabAttendance::where('member_id', $record->uuid)->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Sudah tercatat hadir')
                                ->warning()
                                ->send();
                            return;
                        }

                        PpabAttendance::create([
                            'member_id'  => $record->uuid,
                            'session_id' => $record->id_session ?? null,
                            'created_at' => now()->toDateTimeString(),
                        ]);

                        Notification::make()
                            ->title('Berhasil mencatat kehadiran')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('hapus_absensi')
                        ->label('Hapus Absensi')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Absensi')
                        ->modalDescription('Absensi peserta yang dipilih akan dihapus. Peserta bisa diabsen ulang setelah ini.')
                        ->modalSubmitActionLabel('Ya, Hapus Absensi')
                        ->action(function (Collection $records): void {
                            $uuids = $records->pluck('uuid')->filter()->toArray();
                            if (empty($uuids)) {
                                Notification::make()
                                    ->title('Tidak ada UUID peserta yang valid')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            $deleted = PpabAttendance::whereIn('member_id', $uuids)->delete();
                            Notification::make()
                                ->title("Berhasil menghapus {$deleted} data absensi")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->authorize(fn (): bool => Gate::allows('deleteAny', PpabAttendance::class)),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->paginated([25, 50, 100])
            ->emptyStateHeading('Belum ada peserta PPAB')
            ->emptyStateDescription('Peserta dengan nama angkatan null dan sudah lunas akan tampil di sini.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePpabAttendances::route('/'),
        ];
    }
}
