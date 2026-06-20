<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DaftarFeeTransaksi extends Page
{
    use HasPageShield;
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Daftar Fee Transaksi (Old)';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.placeholder';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
