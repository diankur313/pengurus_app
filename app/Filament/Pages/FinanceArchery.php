<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class FinanceArchery extends Page
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Archery';
    protected static ?string $title = 'Archery Finance';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.placeholder';
}
