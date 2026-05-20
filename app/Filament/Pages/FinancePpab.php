<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class FinancePpab extends Page
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'PPAB';
    protected static ?string $title = 'PPAB Finance';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.placeholder';
}
