<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Models\MemberLama;

class AllCivitas extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'All Civitas';
    protected static ?string $title = 'All Civitas';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.all-civitas';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MemberLama::query()->whereNotNull('member_no')->where('member_no', '!=', ''))
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->member_name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->member_name)),
                TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_gend')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_emai')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_nama_angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
