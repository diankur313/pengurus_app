<?php

namespace App\Filament\Resources\HasilQuizResource\Pages;

use App\Filament\Resources\HasilQuizResource;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\QuizAnswer;
use App\Models\CivitasPendidikan;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class DetailHasilQuiz extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HasilQuizResource::class;
    protected static string $view = 'filament.pages.detail-hasil-quiz';

    // Store primitive values only — Livewire safe
    public int    $quizId           = 0;
    public string $quizTitle        = '';
    public string $angkatan         = '';
    public bool   $hasEssay         = false;
    public int    $totalSiswa       = 0;
    public int    $totalSubmissions  = 0;
    public int    $belumReview      = 0;

    public function mount(int|string $record): void
    {
        $quiz = Quiz::with(['schedules', 'questions'])->findOrFail($record);

        $this->quizId    = $quiz->id;
        $this->quizTitle = $quiz->title;

        $schedule = $quiz->schedules->first();
        $this->angkatan = $schedule ? match ($schedule->level) {
            'dasar'    => 'Angkatan Dasar',
            'lanjutan' => 'Angkatan Lanjutan',
            default    => ucfirst($schedule->level),
        } : '-';

        if ($schedule) {
            $this->totalSiswa = CivitasPendidikan::where('level_angkatan', $schedule->level)->count();
        }

        $this->totalSubmissions = QuizSubmission::where('quiz_id', $quiz->id)->count();
        $this->hasEssay         = $quiz->questions->where('type', 'essay')->isNotEmpty();

        if ($this->hasEssay) {
            $this->belumReview = QuizSubmission::where('quiz_id', $quiz->id)
                ->whereNull('essay_score')
                ->where('status', 'submitted')
                ->count();
        }
    }

    public function table(Table $table): Table
    {
        $quizId   = $this->quizId;
        $hasEssay = $this->hasEssay;

        return $table
            ->query(
                QuizSubmission::query()
                    ->where('quiz_id', $quizId)
                    ->with(['civitas', 'schedule'])
            )
            ->columns([
                TextColumn::make('nama_siswa')
                    ->label('Nama Siswa')
                    ->getStateUsing(fn(QuizSubmission $record): string => $record->civitas?->name ?? '-'),

                TextColumn::make('civitas_angkatan')
                    ->label('Angkatan')
                    ->getStateUsing(fn(QuizSubmission $record): string => $record->civitas?->angkatan ?? '-')
                    ->badge()
                    ->color('info'),

                TextColumn::make('schedule.title')
                    ->label('Jadwal / Sesi'),

                TextColumn::make('durasi_pengerjaan')
                    ->label('Durasi Pengerjaan')
                    ->getStateUsing(function(QuizSubmission $record): string {
                        if (!$record->started_at || !$record->submitted_at) return '-';
                        $diff = $record->started_at->diff($record->submitted_at);
                        return sprintf('%02d:%02d', $diff->i + ($diff->h * 60), $diff->s);
                    }),

                TextColumn::make('total_score')
                    ->label('Score Total')
                    ->formatStateUsing(fn($state) => $state !== null ? number_format((float)$state, 1) : '-')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('mc_score')
                    ->label('PG Score')
                    ->formatStateUsing(fn($state) => $state !== null ? number_format((float)$state, 1) : '-')
                    ->alignCenter(),

                TextColumn::make('essay_score')
                    ->label('Essay Score')
                    ->getStateUsing(function(QuizSubmission $record) use ($hasEssay): string {
                        if (!$hasEssay) return '-';
                        if ($record->essay_score === null) return 'Belum Dinilai';
                        return number_format((float)$record->essay_score, 1);
                    })
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Belum Dinilai' ? 'warning' : 'success')
                    ->visible(fn(): bool => $hasEssay)
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'submitted' => 'success',
                        'reviewed'  => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'submitted' => 'Selesai',
                        'reviewed'  => 'Sudah Ditinjau',
                        default     => ucfirst($state),
                    }),

                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                // ── Review Essay Action ──────────────────────────────────────
                Tables\Actions\Action::make('review_essay')
                    ->label('Review Essay')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn(QuizSubmission $record): bool => $hasEssay)
                    ->modalHeading(fn(QuizSubmission $record): string =>
                        'Review Essay — ' . ($record->civitas?->name ?? '-')
                    )
                    ->modalWidth('4xl')
                    ->fillForm(fn(QuizSubmission $record): array => [
                        'essay_score' => $record->essay_score,
                    ])
                    ->form(function(QuizSubmission $record) {
                        // Build essay answers preview
                        $essayAnswers = QuizAnswer::query()
                            ->where('quiz_submission_id', $record->id)
                            ->with('question')
                            ->whereHas('question', fn($q) => $q->where('type', 'essay'))
                            ->orderBy('id')
                            ->get();

                        $htmlBlocks = [];
                        foreach ($essayAnswers as $i => $answer) {
                            $no       = $i + 1;
                            $soal     = e($answer->question?->question_text ?? '-');
                            $jawaban  = e($answer->answer_text ?? '(Tidak dijawab)');
                            $htmlBlocks[] =
                                '<div style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:12px;">'
                                . '<div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Soal ' . $no . '</div>'
                                . '<div style="font-size:14px;color:#111827;margin-bottom:10px;">' . $soal . '</div>'
                                . '<div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:4px;">Jawaban Siswa</div>'
                                . '<div style="font-size:13px;color:#1d4ed8;background:#eff6ff;border-radius:6px;padding:10px;white-space:pre-wrap;">' . $jawaban . '</div>'
                                . '</div>';
                        }

                        $html = implode('', $htmlBlocks) ?: '<p style="color:#6b7280;">Tidak ada soal essay.</p>';

                        return [
                            Forms\Components\Placeholder::make('essay_preview')
                                ->label('Jawaban Essay Siswa')
                                ->content(new HtmlString('<div style="max-height:380px;overflow-y:auto;">' . $html . '</div>')),

                            Forms\Components\TextInput::make('essay_score')
                                ->label('Nilai Essay (0 – 100)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.5)
                                ->required()
                                ->suffix('poin')
                                ->helperText('Masukkan nilai untuk semua soal essay pada pengerjaan ini.'),
                        ];
                    })
                    ->action(function(QuizSubmission $record, array $data): void {
                        $essayScore = (float) $data['essay_score'];
                        $mcScore    = (float) ($record->mc_score ?? 0);
                        $totalScore = $mcScore + $essayScore;

                        $record->update([
                            'essay_score' => $essayScore,
                            'total_score' => $totalScore,
                            'status'      => 'reviewed',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        // Refresh belumReview count
                        $this->belumReview = QuizSubmission::where('quiz_id', $this->quizId)
                            ->whereNull('essay_score')
                            ->where('status', 'submitted')
                            ->count();
                    })
                    ->successNotificationTitle('Essay berhasil dinilai'),

                // ── Detail Jawaban Action ─────────────────────────────────────
                Tables\Actions\Action::make('lihat_jawaban')
                    ->label('Detail Jawaban')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->url(
                        fn(QuizSubmission $record): string =>
                        HasilQuizResource::getUrl('jawaban-siswa', [
                            'record'     => $quizId,
                            'submission' => $record->id,
                        ])
                    ),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('← Kembali ke Daftar Quiz')
                ->url(HasilQuizResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
