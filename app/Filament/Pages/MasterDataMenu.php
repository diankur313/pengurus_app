<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class MasterDataMenu extends Page
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Master Data';
    protected static ?string $title = 'Master Data';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.placeholder';
}
