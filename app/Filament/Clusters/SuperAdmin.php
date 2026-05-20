<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class SuperAdmin extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Super Admin';
    protected static ?int $navigationSort = 1;
}
