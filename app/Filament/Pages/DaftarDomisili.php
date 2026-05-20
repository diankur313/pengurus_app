<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DaftarDomisili extends Page
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Daftar Domisili';
    protected static ?int $navigationSort = 7;
    protected static string $view = 'filament.pages.placeholder';
}
