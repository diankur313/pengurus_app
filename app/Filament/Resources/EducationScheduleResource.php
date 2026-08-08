<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EducationScheduleResource\Pages;
use App\Models\EducationSchedule;
use App\Models\User;
use App\Services\GoogleMeetService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EducationScheduleResource extends Resource
{
    protected static ?string $model = EducationSchedule::class;

    protected static ?string $navigationIcon    = 'heroicon-o-calendar';
    protected static ?string $navigationLabel   = 'Jadwal Pembelajaran';
    protected static ?string $navigationGroup   = 'Pendidikan';
    protected static ?string $modelLabel        = 'Jadwal Pembelajaran';
    protected static ?string $pluralModelLabel  = 'Jadwal Pembelajaran';
    protected static ?int    $navigationSort    = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ─── Row 1: Tipe + Kehadiran ───────────────────────────────
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

                // ─── Ustadz (jika Pembelajaran) ────────────────────────────
                Forms\Components\Select::make('teacher_id')
                    ->label('Ustadz')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === 'pembelajaran')
                    ->searchable()
                    ->preload(),

                // ─── Quiz (jika Quiz) ───────────────────────────────────────
                Forms\Components\Select::make('quiz_id')
                    ->label('Pilih Quiz')
                    ->options(function (?EducationSchedule $record) {
                        $usedQuizIds = EducationSchedule::query()
                            ->whereNotNull('quiz_id')
                            ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                            ->pluck('quiz_id')
                            ->toArray();

                        return \App\Models\Quiz::where('is_published', true)
                            ->whereNotIn('id', $usedQuizIds)
                            ->pluck('title', 'id');
                    })
                    ->visible(fn (Forms\Get $get) => $get('type') === 'quiz')
                    ->required(fn (Forms\Get $get) => $get('type') === 'quiz')
                    ->searchable()
                    ->helperText('Hanya quiz yang belum digunakan akan ditampilkan'),

                // ─── Judul ─────────────────────────────────────────────────
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),

                // ─── Angkatan ──────────────────────────────────────────────
                Forms\Components\Select::make('level')
                    ->label('Angkatan')
                    ->options(EducationSchedule::levelOptions())
                    ->required()
                    ->searchable(),

                // ─── Waktu ─────────────────────────────────────────────────
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DateTimePicker::make('start_at')
                        ->label('Mulai')
                        ->required()
                        ->live()
                        ->rules([
                            fn (?EducationSchedule $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                if (empty($value)) return;
                                $newDate = \Illuminate\Support\Carbon::parse($value);
                                $today = now()->startOfDay();
                                
                                if (!$record || !$record->exists) {
                                    if ($newDate->isBefore($today)) {
                                        $fail('Tanggal mulai tidak boleh di masa lalu.');
                                    }
                                } else {
                                    $originalStart = $record->getOriginal('start_at');
                                    if ($originalStart) {
                                        $origDate = \Illuminate\Support\Carbon::parse($originalStart);
                                        if ($newDate->format('Y-m-d H:i') !== $origDate->format('Y-m-d H:i') && $newDate->isBefore($today)) {
                                            $fail('Tanggal mulai tidak boleh di masa lalu.');
                                        }
                                    }
                                }
                            }
                        ]),

                    Forms\Components\DateTimePicker::make('end_at')
                        ->label('Selesai')
                        ->required()
                        ->afterOrEqual('start_at'),
                ]),

                // ─── Pengaturan Google Meet ─────────────────────────────────
                Forms\Components\Section::make('🎥 Pengaturan Google Meet')
                    ->description('Opsi ini hanya tersedia ketika mode Kehadiran diset ke Online.')
                    ->schema([

                        // Meeting link (readonly, auto-generated via OAuth2)
                        Forms\Components\TextInput::make('meeting_link')
                            ->label('Meeting Link')
                            ->helperText('Otomatis dibuat setelah jadwal disimpan sebagai Online.')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Akan terisi otomatis…'),

                        // Co-Host
                        Forms\Components\Select::make('meet_co_host_email')
                            ->label('Co-Host')
                            ->helperText('Pengguna yang bisa mengelola meeting. Harus memiliki akun Google.')
                            ->options(function () {
                                $currentUser = Auth::user();
                                if (!$currentUser) {
                                    return [];
                                }
                                return User::whereHas('roles', function ($q) use ($currentUser) {
                                    $roleNames = $currentUser->roles->pluck('name');
                                    $q->whereIn('name', $roleNames);
                                })
                                    ->where('id', '!=', $currentUser->id)
                                    ->pluck('email', 'email');
                            })
                            ->searchable()
                            ->nullable()
                            ->placeholder('Pilih co-host (opsional)'),

                        // Akses Meeting
                        Forms\Components\Radio::make('meet_access_type')
                            ->label('Akses Meeting')
                            ->options([
                                'OPEN'       => 'Terbuka — semua bisa join langsung',
                                'TRUSTED'    => 'Terpercaya — internal langsung, external harus knock',
                                'RESTRICTED' => 'Terbatas — semua harus minta izin masuk',
                            ])
                            ->default('OPEN')
                            ->required(),

                        // Moderasi
                        Forms\Components\Radio::make('meet_moderation')
                            ->label('Moderasi')
                            ->options([
                                'OFF'         => 'Tanpa moderasi — semua peserta setara',
                                'COHOST_ONLY' => 'Hanya Host & Co-Host yang bisa kontrol',
                            ])
                            ->default('OFF')
                            ->required(),


                        // Deskripsi
                        Forms\Components\Textarea::make('meet_description')
                            ->label('Deskripsi Meeting')
                            ->helperText('Tampil di detail event Google Calendar. Bisa berisi instruksi atau link materi.')
                            ->rows(3)
                            ->placeholder('Contoh: Harap siapkan buku catatan. Materi tersedia di portal e-SII.'),

                        // ─── Reminder Email ─────────────────────────────────
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Toggle::make('send_reminder')
                                ->label('📧 Kirim Reminder Email')
                                ->helperText('Email pengingat ke semua peserta sesuai angkatan jadwal.')
                                ->default(false)
                                ->live(),

                            Forms\Components\TextInput::make('reminder_before')
                                ->label('Waktu Reminder')
                                ->helperText('Format HH:MM sebelum jadwal dimulai. Contoh: 00:15 = 15 menit, 01:00 = 1 jam.')
                                ->placeholder('00:15')
                                ->default('00:15')
                                ->regex('/^\d{2}:\d{2}$/')
                                ->visible(fn (Forms\Get $get) => (bool) $get('send_reminder')),
                        ]),

                    ])
                    ->visible(fn (Forms\Get $get) => $get('attendance_mode') === 'online')
                    ->collapsible(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime('d M Y, H:i')
                    ->label('Mulai')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'info'    => 'pembelajaran',
                        'warning' => 'quiz',
                    ]),

                Tables\Columns\BadgeColumn::make('attendance_mode')
                    ->label('Kehadiran')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'success' => 'online',
                        'gray'    => 'offline',
                    ]),

                Tables\Columns\ViewColumn::make('meeting_link')
                    ->label('Google Meet')
                    ->view('filament.tables.columns.meeting-link')
                    ->width('200px'),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Ustadz'),

                Tables\Columns\BadgeColumn::make('level')
                    ->label('Angkatan')
                    ->formatStateUsing(fn (string $state) => EducationSchedule::levelOptions()[$state] ?? ucfirst($state))
                    ->colors([
                        'success' => 'general',
                        'primary' => 'semester_1',
                        'info'    => 'semester_2',
                        'warning' => 'semester_3',
                    ])
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function (EducationSchedule $record, array $data, Tables\Actions\EditAction $action) {
                        // Online → Offline: hapus Calendar event + Meet space
                        if (
                            $record->attendance_mode === 'online' &&
                            ($data['attendance_mode'] ?? 'offline') === 'offline' &&
                            $record->google_event_id
                        ) {
                            try {
                                $service = new GoogleMeetService();
                                $service->deleteMeeting($record->google_event_id, $record->google_space_name);
                            } catch (\Exception $e) {
                                // silent
                            }
                            $record->update([
                                'meeting_link'      => null,
                                'google_event_id'   => null,
                                'google_space_name' => null,
                                'reminder_sent'     => false,
                            ]);
                        }
                    })
                    ->after(function (EducationSchedule $record, $livewire) {
                        if ($record->attendance_mode !== 'online') {
                            $livewire->dispatch('refreshCalendar');
                            return;
                        }

                        // Offline → Online (belum ada link): generate
                        if (!$record->meeting_link) {
                            static::generateMeetLink($record);
                            $livewire->dispatch('refreshCalendar');
                            return;
                        }

                        // Tetap Online: update Calendar event
                        try {
                            $service = new GoogleMeetService();
                            $updated = $service->updateMeeting($record);

                            if (!$updated) {
                                // Event 404 — re-create
                                $record->update([
                                    'meeting_link'      => null,
                                    'google_event_id'   => null,
                                    'google_space_name' => null,
                                ]);
                                static::generateMeetLink($record);

                                Notification::make()
                                    ->title('Meeting link lama expired')
                                    ->body('Link baru telah dibuat otomatis.')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal update Google Meet')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }

                        $record->update(['reminder_sent' => false]);
                        $livewire->dispatch('refreshCalendar');
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (EducationSchedule $record) {
                        if ($record->google_event_id) {
                            try {
                                $service = new GoogleMeetService();
                                $service->deleteMeeting($record->google_event_id, $record->google_space_name);
                            } catch (\Exception $e) {
                                // silent
                            }
                        }
                    })
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshCalendar');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($livewire) {
                            $livewire->dispatch('refreshCalendar');
                        }),
                ]),
            ]);
    }

    /**
     * Generate Meet link + Calendar event untuk jadwal Online.
     */
    protected static function generateMeetLink(EducationSchedule $record): void
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEducationSchedules::route('/'),
        ];
    }
}
