<?php

namespace App\Filament\Widgets;

use App\Models\Holiday;
use Carbon\Carbon;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class HolidayCalendarWidget extends FullCalendarWidget
{
    protected static bool $isDiscovered = false;

    public Model|string|null $model = Holiday::class;

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
                    $start = $arguments['start'] ?? null;
                    $end   = $arguments['end'] ?? null;

                    // FullCalendar sends end as exclusive, so subtract 1 day
                    $startDate = $start ? Carbon::parse($start)->toDateString() : null;
                    $endDate   = $end ? Carbon::parse($end)->subDay()->toDateString() : $startDate;

                    $form->fill([
                        'date'     => $startDate,
                        'end_date' => $endDate,
                    ]);
                })
                ->extraAttributes(['class' => 'hidden']),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Holiday::query()
            ->where('date', '>=', $fetchInfo['start'])
            ->where('date', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn (Holiday $h) => [
                'id'     => $h->id,
                'title'  => $h->title,
                'start'  => $h->date->toDateString(),
                'allDay' => true,
                'color'  => '#dc2626',
            ])
            ->all();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_holiday') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('update_holiday') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete_holiday') ?? false;
    }

    /**
     * When the create form is submitted, loop through the date range
     * and create one Holiday record per day (upsert to avoid duplicates).
     */
    protected function onFormSubmitted(): void
    {
        $data  = $this->form->getState();
        $start = $data['date'] ?? null;
        $end   = $data['end_date'] ?? $start;

        if ($start) {
            $cursor = Carbon::parse($start);
            $endDt  = Carbon::parse($end);

            while ($cursor->lte($endDt)) {
                Holiday::updateOrCreate(
                    ['date' => $cursor->toDateString()],
                    [
                        'title'       => $data['title'] ?? '',
                        'description' => $data['description'] ?? null,
                    ],
                );
                $cursor->addDay();
            }
        }

        $this->refreshEvents();
    }

    protected function onEventDeleted(): void
    {
        $this->refreshEvents();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('date')
                ->label('Tanggal Mulai')
                ->required()
                ->native(false)
                ->extraAttributes([
                    'onkeydown' => 'return false;',
                    'style'     => 'caret-color: transparent;',
                ]),

            Forms\Components\DatePicker::make('end_date')
                ->label('Tanggal Akhir')
                ->required()
                ->native(false)
                ->afterOrEqual('date')
                ->extraAttributes([
                    'onkeydown' => 'return false;',
                    'style'     => 'caret-color: transparent;',
                ]),

            Forms\Components\TextInput::make('title')
                ->label('Nama Hari Libur')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: Idul Fitri 1447H'),

            Forms\Components\Textarea::make('description')
                ->label('Keterangan')
                ->nullable()
                ->rows(2)
                ->placeholder('Keterangan tambahan (opsional)'),
        ];
    }
}
