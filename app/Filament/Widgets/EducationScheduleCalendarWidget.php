<?php

namespace App\Filament\Widgets;

use App\Models\EducationSchedule;
use App\Models\User;
use App\Services\GoogleMeetService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Livewire\Attributes\On;

class EducationScheduleCalendarWidget extends FullCalendarWidget
{
    /**
     * Matikan auto-discovery agar widget tidak muncul di Dashboard secara otomatis
     */
    protected static bool $isDiscovered = false;

    public Model|string|null $model = EducationSchedule::class;

    /**
     * Konfigurasi FullCalendar
     */
    public function config(): array
    {
        return [
            'displayEventTime' => false,
            'eventDisplay'     => 'block',
            'selectable'       => true,
        ];
    }

    protected function headerActions(): array
    {
        return [
            \Saade\FilamentFullCalendar\Actions\CreateAction::make()
                ->mountUsing(function (\Filament\Forms\Form $form, array $arguments) {
                    $start = isset($arguments['start']) ? \Illuminate\Support\Carbon::parse($arguments['start']) : null;
                    $end   = isset($arguments['end'])   ? \Illuminate\Support\Carbon::parse($arguments['end'])   : null;

                    $dateRangeDisplay = '';
                    $isSingleDay      = false;

                    if ($start && $end) {
                        $isSameDay   = $start->format('Y-m-d') === $end->format('Y-m-d');
                        $isNextDay   = $end->copy()->subDay()->format('Y-m-d') === $start->format('Y-m-d');
                        $isSingleDay = $isSameDay || $isNextDay;

                        if ($isSingleDay) {
                            $dateRangeDisplay = $start->format('d-m-Y');
                        } else {
                            $displayEnd       = $end->copy()->subDay();
                            $dateRangeDisplay = $start->format('d-m-Y') . ' s/d ' . $displayEnd->format('d-m-Y');
                        }
                    }

                    $formStart = $start;
                    $formEnd   = $end;

                    if ($start && $end) {
                        if ($isSingleDay) {
                            $formStart = $start->copy()->setTime(8, 0);
                            $formEnd   = $start->copy()->setTime(10, 0);
                        } else {
                            $formStart = $start->copy()->setTime(8, 0);
                            $formEnd   = $end->copy()->subDay()->setTime(10, 0);
                        }
                    }

                    $form->fill([
                        'selected_date_range' => $dateRangeDisplay,
                        'start_at'            => $formStart?->format('Y-m-d H:i') ?? null,
                        'end_at'              => $formEnd?->format('Y-m-d H:i') ?? null,
                        'attendance_mode'     => 'offline',
                    ]);
                })
                ->after(function (EducationSchedule $record) {
                    // Jika Online: auto-generate Meet link
                    if ($record->attendance_mode === 'online' && !$record->meeting_link) {
                        static::generateMeetLinkForRecord($record);
                    }
                    $this->dispatch('filament-fullcalendar--refresh');
                    $this->dispatch('refreshTable');
                })
                ->extraAttributes(['class' => 'hidden']),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return EducationSchedule::query()
            ->where('start_at', '>=', $fetchInfo['start'])
            ->where('end_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (EducationSchedule $schedule) {
                $angkatan = $schedule->level === 'general' ? 'General' : ($schedule->level === 'dasar' ? 'Dasar' : 'Lanjutan');
                $durasi      = $schedule->start_at->format('H:i') . ' - ' . $schedule->end_at->format('H:i');
                $ustadz      = $schedule->teacher ? ' (' . $schedule->teacher->name . ')' : '';
                $kehadiran   = $schedule->attendance_mode === 'online' ? ' 🎥' : '';
                $meetStatus  = ($schedule->attendance_mode === 'online' && $schedule->meeting_link) ? ' ✓' : '';

                return [
                    'id'    => $schedule->id,
                    'title' => "Angkatan: {$angkatan}\nJudul: {$schedule->title}{$ustadz}{$kehadiran}{$meetStatus}\nDurasi: {$durasi}",
                    'start' => $schedule->start_at,
                    'end'   => $schedule->end_at,
                    'allDay' => false,
                    'color'  => $schedule->type === 'pembelajaran' ? '#0369a1' : '#b91c1c',
                ];
            })
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

    #[On('refreshCalendar')]
    public function refreshCalendar(): void
    {
        $this->dispatch('filament-fullcalendar--refresh');
    }

    protected function onFormSubmitted(): void
    {
        $this->dispatch('filament-fullcalendar--refresh');
        $this->dispatch('refreshTable');
    }

    protected function onEventDeleted(): void
    {
        $this->dispatch('filament-fullcalendar--refresh');
        $this->dispatch('refreshTable');
    }

    protected function modalActions(): array
    {
        return [
            \Saade\FilamentFullCalendar\Actions\EditAction::make()
                ->after(function (EducationSchedule $record) {
                    if ($record->attendance_mode === 'online') {
                        if (!$record->meeting_link) {
                            static::generateMeetLinkForRecord($record);
                        } else {
                            try {
                                $service = new GoogleMeetService();
                                $service->updateMeeting($record);
                            } catch (\Exception $e) {
                                // silent
                            }
                        }
                    }
                    $this->dispatch('refreshTable');
                }),
            \Saade\FilamentFullCalendar\Actions\DeleteAction::make()
                ->before(function (EducationSchedule $record) {
                    // End Meet space + hapus Calendar event sebelum record dihapus
                    if ($record->google_event_id) {
                        try {
                            $service = new GoogleMeetService();
                            $service->deleteMeeting($record->google_event_id, $record->google_space_name);
                        } catch (\Exception $e) {
                            // silent — jangan block delete
                        }
                    }
                })
                ->after(function () {
                    $this->dispatch('filament-fullcalendar--refresh');
                    $this->dispatch('refreshTable');
                }),
        ];
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('selected_date_range')
                ->label('Tanggal yang Dipilih')
                ->readonly()
                ->dehydrated(false),

            // ─── Row 1: Tipe + Kehadiran ──────────────────────────────────
            Forms\Components\Grid::make(2)->schema([

                Forms\Components\Radio::make('type')
                    ->label('Tipe')
                    ->options([
                        'pembelajaran' => 'Pembelajaran',
                        'quiz'         => 'Quiz',
                    ])
                    ->required()
                    ->live()
                    ->inline(),

                Forms\Components\Radio::make('attendance_mode')
                    ->label('Kehadiran')
                    ->options([
                        'offline' => 'Offline',
                        'online'  => 'Online',
                    ])
                    ->default('offline')
                    ->required()
                    ->live()
                    ->inline(),
            ]),

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

            Forms\Components\Select::make('level')
                ->label('Angkatan')
                ->options([
                    'general'  => 'General (Dasar dan Lanjutan)',
                    'dasar'    => 'Angkatan Dasar',
                    'lanjutan' => 'Angkatan Lanjutan',
                ])
                ->required()
                ->searchable(),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DateTimePicker::make('start_at')
                    ->label('Mulai')
                    ->required()
                    ->seconds(false)
                    ->live(),
                Forms\Components\DateTimePicker::make('end_at')
                    ->label('Selesai')
                    ->required()
                    ->seconds(false),
            ]),

            // ─── Google Meet Settings ─────────────────────────────────────
            Forms\Components\Section::make('🎥 Pengaturan Google Meet')
                ->schema([

                    Forms\Components\TextInput::make('meeting_link')
                        ->label('Meeting Link')
                        ->helperText('Otomatis dibuat setelah jadwal disimpan.')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Akan terisi otomatis…'),

                    Forms\Components\Select::make('meet_co_host_email')
                        ->label('Co-Host')
                        ->helperText('Pengguna yang bisa mengelola meeting.')
                        ->options(function () {
                            $currentUser = Auth::user();
                            if (!$currentUser) return [];
                            return User::whereHas('roles', function ($q) use ($currentUser) {
                                $q->whereIn('name', $currentUser->roles->pluck('name'));
                            })
                                ->where('id', '!=', $currentUser->id)
                                ->pluck('email', 'email');
                        })
                        ->searchable()
                        ->nullable()
                        ->placeholder('Pilih co-host (opsional)'),

                    Forms\Components\Radio::make('meet_access_type')
                        ->label('Akses Meeting')
                        ->options([
                            'OPEN'       => 'Terbuka — semua bisa join langsung',
                            'TRUSTED'    => 'Terpercaya — internal langsung, external harus knock',
                            'RESTRICTED' => 'Terbatas — semua harus minta izin masuk',
                        ])
                        ->default('OPEN')
                        ->required(),

                    Forms\Components\Radio::make('meet_moderation')
                        ->label('Moderasi')
                        ->options([
                            'OFF'         => 'Tanpa moderasi — semua peserta setara',
                            'COHOST_ONLY' => 'Hanya Host & Co-Host yang bisa kontrol',
                        ])
                        ->default('OFF')
                        ->required(),

                    Forms\Components\Textarea::make('meet_description')
                        ->label('Deskripsi Meeting')
                        ->helperText('Tampil di detail event Google Calendar.')
                        ->rows(3)
                        ->placeholder('Contoh: Harap siapkan buku catatan.'),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Toggle::make('send_reminder')
                            ->label('📧 Kirim Reminder Email')
                            ->default(false)
                            ->live(),

                        Forms\Components\TextInput::make('reminder_before')
                            ->label('Waktu Reminder')
                            ->helperText('Format HH:MM sebelum jadwal.')
                            ->placeholder('00:15')
                            ->default('00:15')
                            ->regex('/^\d{2}:\d{2}$/')
                            ->visible(fn (Forms\Get $get) => (bool) $get('send_reminder')),
                    ]),
                ])
                ->visible(fn (Forms\Get $get) => $get('attendance_mode') === 'online')
                ->collapsible(),
        ];
    }

    /**
     * Generate Meet link + Calendar event untuk record baru.
     */
    protected static function generateMeetLinkForRecord(EducationSchedule $record): void
    {
        try {
            $service = new GoogleMeetService();
            [$link, $eventId, $spaceName] = $service->createMeeting($record);

            if ($link) {
                $record->update([
                    'meeting_link'      => $link,
                    'google_event_id'   => $eventId,
                    'google_space_name' => $spaceName,
                ]);

                Notification::make()
                    ->title('Google Meet berhasil dibuat')
                    ->body('Link: ' . $link)
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal membuat Google Meet')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
