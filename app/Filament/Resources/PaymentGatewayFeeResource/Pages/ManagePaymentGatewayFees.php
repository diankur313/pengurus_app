<?php

namespace App\Filament\Resources\PaymentGatewayFeeResource\Pages;

use App\Filament\Resources\PaymentGatewayFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePaymentGatewayFees extends ManageRecords
{
    protected static string $resource = PaymentGatewayFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Aturan Fee'),
        ];
    }
}
