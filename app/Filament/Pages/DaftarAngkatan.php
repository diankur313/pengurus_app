<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DaftarAngkatan extends Page
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Daftar Angkatan';
    protected static ?int $navigationSort = 12;
    protected static string $view = 'filament.pages.placeholder';
}
