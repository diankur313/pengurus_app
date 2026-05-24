<?php

namespace App\Filament\Widgets;

use App\Models\EducationSchedule;
use App\Models\Quiz;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Forms;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class EducationScheduleCalendarWidget extends FullCalendarWidget
{
    /**
     * Matikan auto-discovery agar widget tidak muncul di Dashboard secara otomatis
     */
    protected static bool $isDiscovered = false;

    public Model|string|null $model = EducationSchedule::class;

    /**
     * Konfigurasi FullCalendar untuk menyeragamkan layout
     */
    public function config(): array
    {
        return [
            'displayEventTime' => false,
            'eventDisplay' => 'block',
            'selectable' => true,
        ];
    }

    protected function headerActions(): array
    {
        return [
            \Saade\FilamentFullCalendar\Actions\CreateAction::make()
                ->mountUsing(function (\Filament\Forms\Form $form, array $arguments) {
                    $start = isset($arguments['start']) ? \Illuminate\Support\Carbon::parse($arguments['start']) : null;
                    $end = isset($arguments['end']) ? \Illuminate\Support\Carbon::parse($arguments['end']) : null;
                    
                    // Format untuk tampilan readonly (Rentang Tanggal)
                    $dateRangeDisplay = '';
                    $isSingleDay = false;

                    if ($start && $end) {
                        // Jika klik 1 hari saja, FullCalendar mengirim start=end (misal keduanya 2026-05-25)
                        // ATAU end = start + 1 hari (eksklusif). Kedua kasus = seleksi 1 hari.
                        $isSameDay = $start->format('Y-m-d') === $end->format('Y-m-d');
                        $isNextDay = $end->copy()->subDay()->format('Y-m-d') === $start->format('Y-m-d');
                        $isSingleDay = $isSameDay || $isNextDay;

                        if ($isSingleDay) {
                            $dateRangeDisplay = $start->format('d-m-Y');
                        } else {
                            // Seleksi multi-hari — end eksklusif, kurangi 1 hari untuk tampilan inklusif
                            $displayEnd = $end->copy()->subDay();
                            $dateRangeDisplay = $start->format('d-m-Y') . ' s/d ' . $displayEnd->format('d-m-Y');
                        }
                    }

                    // Set default jam yang masuk akal (08:00 - 10:00)
                    // FullCalendar mengirim 00:00 untuk allDay selection — tidak realistis untuk jadwal
                    $formStart = $start;
                    $formEnd = $end;

                    if ($start && $end) {
                        if ($isSingleDay) {
                            // Klik 1 hari → Mulai 08:00, Selesai 10:00 di hari yang sama
                            $formStart = $start->copy()->setTime(8, 0);
                            $formEnd = $start->copy()->setTime(10, 0);
                        } else {
                            // Multi-hari → Mulai 08:00 hari pertama, Selesai 10:00 hari terakhir (inklusif)
                            $formStart = $start->copy()->setTime(8, 0);
                            $formEnd = $end->copy()->subDay()->setTime(10, 0);
                        }
                    }

                    $form->fill([
                        'selected_date_range' => $dateRangeDisplay,
                        'start_at' => $formStart?->format('Y-m-d H:i') ?? null,
                        'end_at' => $formEnd?->format('Y-m-d H:i') ?? null,
                    ]);
                })
                ->extraAttributes(['class' => 'hidden']), // Sembunyikan tombol di header
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return EducationSchedule::query()
            ->where('start_at', '>=', $fetchInfo['start'])
            ->where('end_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                function (EducationSchedule $schedule) {
                    $angkatan = $schedule->level === 'dasar' ? 'Dasar' : 'Lanjutan';
                    $durasi = $schedule->start_at->format('H:i') . ' - ' . $schedule->end_at->format('H:i');
                    $ustadz = $schedule->teacher ? ' (' . $schedule->teacher->name . ')' : '';

                    return [
                        'id' => $schedule->id,
                        'title' => "Angkatan: {$angkatan}\nJudul: {$schedule->title}{$ustadz}\nDurasi: {$durasi}",
                        'start' => $schedule->start_at,
                        'end' => $schedule->end_at,
                        'allDay' => false,
                        'color' => $schedule->type === 'pembelajaran' ? '#0369a1' : '#b91c1c',
                    ];
                }
            )
            ->all();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_education::schedule') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update_education::schedule') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete_education::schedule') ?? false;
    }

    /**
     * Event yang dipanggil setelah data berhasil disimpan (create/edit)
     */
    protected function onFormSubmitted(): void
    {
        $this->refreshEvents();
    }

    /**
     * Event yang dipanggil setelah data berhasil dihapus
     */
    protected function onEventDeleted(): void
    {
        $this->refreshEvents();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('selected_date_range')
                ->label('Tanggal yang Dipilih')
                ->readonly()
                ->dehydrated(false),

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
                ->relationship('quiz', 'title', fn ($query) => $query->where('is_published', true))
                ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                ->required(fn (Forms\Get $get) => $get('type') === 'quiz')
                ->searchable()
                ->preload()
                ->helperText('Pilih quiz master yang akan digunakan'),

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

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DateTimePicker::make('start_at')
                        ->label('Mulai')
                        ->required()
                        ->seconds(false),
                    Forms\Components\DateTimePicker::make('end_at')
                        ->label('Selesai')
                        ->required()
                        ->seconds(false),
                ]),
        ];
    }
}
