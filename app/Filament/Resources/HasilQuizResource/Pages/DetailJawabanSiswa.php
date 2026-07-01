<?php

namespace App\Filament\Resources\HasilQuizResource\Pages;

use App\Filament\Resources\HasilQuizResource;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\QuizAnswer;
use Filament\Resources\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;

class DetailJawabanSiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HasilQuizResource::class;
    protected static string $view = 'filament.pages.detail-jawaban-siswa';

    // Store only primitives — Livewire safe
    public int    $quizId       = 0;
    public int    $submissionId = 0;
    public string $siswaNama    = '';
    public string $quizTitle    = '';
    public string $totalScore   = '-';
    public string $mcScore      = '-';
    public string $essayScore   = '-';
    public bool   $essayNull    = false;
    public string $siswaPhoto   = '';
    public string $status       = '';
    public bool   $hasEssay     = false;

    public function mount(int|string $record, int|string $submission): void
    {
        $quiz       = Quiz::with(['questions.options'])->findOrFail($record);
        $submission = QuizSubmission::with(['answers.question.options', 'answers.selectedOption', 'civitas'])->findOrFail($submission);

        $this->quizId       = $quiz->id;
        $this->submissionId = $submission->id;
        $this->quizTitle    = $quiz->title;
        $this->siswaNama    = $submission->civitas?->name ?? '-';
        
        $civitasPhoto = $submission->civitas?->photo;
        if (filled($civitasPhoto) && $civitasPhoto !== 'avatar.png') {
            $this->siswaPhoto = profilePhotoUrl($civitasPhoto, $this->siswaNama);
        } else {
            $this->siswaPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($this->siswaNama) . '&color=FFFFFF&background=09090b';
        }

        $this->totalScore   = $submission->total_score !== null ? number_format((float)$submission->total_score, 1) : '-';
        $this->mcScore      = $submission->mc_score !== null ? number_format((float)$submission->mc_score, 1) : '-';
        $this->essayNull    = $submission->essay_score === null;
        $this->essayScore   = $submission->essay_score !== null ? number_format((float)$submission->essay_score, 1) : '-';
        $this->status       = $submission->status;
        $this->hasEssay     = $quiz->questions->where('type', 'essay')->isNotEmpty();
    }

    public function table(Table $table): Table
    {
        $submissionId = $this->submissionId;

        return $table
            ->query(
                QuizAnswer::query()
                    ->where('quiz_submission_id', $submissionId)
                    ->with(['question.options', 'selectedOption'])
                    ->join('quiz_questions', 'quiz_answers.quiz_question_id', '=', 'quiz_questions.id')
                    ->orderBy('quiz_questions.order')
                    ->select('quiz_answers.*')
            )
            ->columns([
                TextColumn::make('question.order')
                    ->label('No.')
                    ->alignCenter()
                    ->width('60px'),

                TextColumn::make('question.type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'essay' ? 'Essay' : 'Pilihan Ganda')
                    ->color(fn (string $state): string => $state === 'essay' ? 'warning' : 'info')
                    ->width('130px'),

                TextColumn::make('question.question_text')
                    ->label('Pertanyaan')
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('jawaban_siswa')
                    ->label('Jawaban Siswa')
                    ->getStateUsing(function (QuizAnswer $record): string {
                        if ($record->question?->type === 'essay') {
                            return $record->answer_text ?? '(Tidak dijawab)';
                        }
                        return $record->selectedOption?->option_text ?? '(Tidak dijawab)';
                    })
                    ->color(fn (QuizAnswer $record): string => match (true) {
                        $record->question?->type === 'essay' => 'gray',
                        $record->is_correct === true         => 'success',
                        default                              => 'danger',
                    })
                    ->wrap(),

                TextColumn::make('kunci_jawaban')
                    ->label('Kunci Jawaban')
                    ->getStateUsing(function (QuizAnswer $record): string {
                        if ($record->question?->type === 'essay') {
                            return '(Dinilai Manual)';
                        }
                        $correct = $record->question?->options->where('is_correct', true)->first();
                        return $correct?->option_text ?? '-';
                    })
                    ->color(fn (string $state): string => $state === '(Dinilai Manual)' ? 'gray' : 'success')
                    ->wrap(),

                IconColumn::make('is_correct')
                    ->label('Hasil')
                    ->getStateUsing(function (QuizAnswer $record): ?bool {
                        if ($record->question?->type === 'essay') return null;
                        return $record->is_correct;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('← Kembali ke Detail Quiz')
                ->url(HasilQuizResource::getUrl('detail', ['record' => $this->quizId]))
                ->color('gray'),
        ];
    }
}
