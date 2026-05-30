<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HolidayCalendarWidget;
use App\Models\Holiday;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;

class DaftarHariLibur extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Daftar Hari Libur';
    protected static ?string $title           = 'Daftar Hari Libur';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.pages.daftar-hari-libur';

    protected function getHeaderWidgets(): array
    {
        return [
            HolidayCalendarWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Holiday::query())
            ->defaultSort('date', 'asc')
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->placeholder('Tidak ada keterangan')
                    ->limit(50),
            ])
            ->headerActions([])
            ->actions([
                EditAction::make()
                    ->form($this->getHolidayFormSchema())
                    ->after(fn () => $this->dispatch('refreshEvents')),
                DeleteAction::make()
                    ->after(fn () => $this->dispatch('refreshEvents')),
            ]);
    }

    protected function getHolidayFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->native(false)
                ->unique(Holiday::class, 'date', ignorable: fn (?Model $record) => $record)
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
