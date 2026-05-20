<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class CivitasMenu extends Page
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Civitas';
    protected static ?string $title = 'Civitas';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.placeholder';
}
