<?php

namespace App\Filament\Widgets;

use App\Models\EducationSchedule;
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
                    if ($start && $end) {
                        // FullCalendar 'end' biasanya eksklusif (hari berikutnya 00:00), 
                        // kita kurangi 1 hari untuk tampilan rentang yang inklusif jika allDay
                        $displayEnd = $end->copy()->subDay();
                        if ($start->format('Y-m-d') === $displayEnd->format('Y-m-d')) {
                            $dateRangeDisplay = $start->format('d-m-Y');
                        } else {
                            $dateRangeDisplay = $start->format('d-m-Y') . ' s/d ' . $displayEnd->format('d-m-Y');
                        }
                    }

                    $form->fill([
                        'selected_date_range' => $dateRangeDisplay,
                        'start_at' => $start?->format('Y-m-d H:i') ?? null,
                        'end_at' => $end?->format('Y-m-d H:i') ?? null,
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
