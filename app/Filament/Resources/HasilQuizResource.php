<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HasilQuizResource\Pages;
use App\Models\Quiz;
use App\Models\CivitasPendidikan;
use App\Models\QuizSubmission;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;




class HasilQuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Hasil Quiz';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Hasil Quiz';
    protected static ?string $pluralModelLabel = 'Hasil Quiz';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'hasil-quiz';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Quiz::query()
                    ->with(['schedules', 'questions'])
                    ->withCount('submissions')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Quiz')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->alignCenter(),

                TextColumn::make('duration')
                    ->label('Durasi')
                    ->sortable(),

                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->getStateUsing(function (Quiz $record): string {
                        $schedule = $record->schedules->first();
                        if (!$schedule) return '-';
                        return $schedule->levelLabel();
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Semester 1' => 'info',
                        'Semester 2' => 'warning',
                        'Semester 3' => 'danger',
                        default      => 'gray',
                    }),

                TextColumn::make('progress')
                    ->label('Progress Pengerjaan')
                    ->getStateUsing(function (Quiz $record): string {
                        $schedule = $record->schedules->first();
                        if (!$schedule) {
                            return '0|0|0';
                        }
                        $total   = CivitasPendidikan::whereIn('level_angkatan', $schedule->levelTargets())->count();
                        $done    = QuizSubmission::where('quiz_id', $record->id)->count();
                        $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                        return "{$done}|{$total}|{$percent}";
                    })
                    ->formatStateUsing(function (string $state): \Illuminate\Support\HtmlString {
                        [$done, $total, $percent] = explode('|', $state);
                        $percent  = (int) $percent;
                        $barColor = match(true) {
                            $percent >= 80 => '#22c55e',
                            $percent >= 50 => '#eab308',
                            default        => '#3b82f6',
                        };
                        return new \Illuminate\Support\HtmlString(
                            '<div style="min-width:140px;max-width:200px;">'
                            . '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">'
                            . '<span style="font-size:11px;color:#6b7280;">' . $done . ' / ' . $total . '</span>'
                            . '<span style="font-size:12px;font-weight:700;color:' . $barColor . ';">' . $percent . '%</span>'
                            . '</div>'
                            . '<div style="height:8px;background:#e5e7eb;border-radius:9999px;overflow:hidden;">'
                            . '<div style="height:100%;width:' . $percent . '%;background:' . $barColor . ';border-radius:9999px;"></div>'
                            . '</div>'
                            . '</div>'
                        );
                    })
                    ->html(),

                TextColumn::make('status_jadwal')
                    ->label('Status')
                    ->getStateUsing(function (Quiz $record): string {
                        $schedule = $record->schedules->first();
                        if (!$schedule || !$schedule->end_at) return 'Tidak Ada Jadwal';
                        return now()->lt($schedule->end_at) ? 'Sedang Berlangsung' : 'Sudah Selesai';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sedang Berlangsung' => 'warning',
                        'Sudah Selesai'      => 'success',
                        default              => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('createdBy.name')
                    ->label('Dibuat oleh'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('angkatan')
                    ->label('Angkatan')
                    ->options([
                        'semester_1' => 'Semester 1',
                        'semester_2' => 'Semester 2',
                        'semester_3' => 'Semester 3',
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder =>
                        filled($data['value'])
                            ? $query->whereHas('schedules', fn ($q) => $q->where('level', $data['value']))
                            : $query
                    ),

                Tables\Filters\SelectFilter::make('status_jadwal')
                    ->label('Status')
                    ->options([
                        'berlangsung' => 'Sedang Berlangsung',
                        'selesai'     => 'Sudah Selesai',
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => match ($data['value'] ?? null) {
                        'berlangsung' => $query->whereHas('schedules', fn ($q) => $q->where('end_at', '>', now())),
                        'selesai'     => $query->whereHas('schedules', fn ($q) => $q->where('end_at', '<=', now())),
                        default       => $query,
                    }),

                Tables\Filters\Filter::make('tanggal_jadwal')
                    ->label('Tanggal Jadwal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->placeholder('Pilih tanggal awal'),
                        \Filament\Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['dari'] ?? null, fn ($q) =>
                                $q->whereHas('schedules', fn ($s) =>
                                    $s->whereDate('start_at', '>=', $data['dari'])
                                )
                            )
                            ->when($data['sampai'] ?? null, fn ($q) =>
                                $q->whereHas('schedules', fn ($s) =>
                                    $s->whereDate('start_at', '<=', $data['sampai'])
                                )
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (!empty($data['dari']))   $indicators['dari']   = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->translatedFormat('d M Y');
                        if (!empty($data['sampai'])) $indicators['sampai'] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->translatedFormat('d M Y');
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Quiz $record): string => static::getUrl('detail', ['record' => $record->id])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'          => Pages\ListHasilQuiz::route('/'),
            'detail'         => Pages\DetailHasilQuiz::route('/{record}/detail'),
            'jawaban-siswa'  => Pages\DetailJawabanSiswa::route('/{record}/submission/{submission}'),
        ];
    }
}
