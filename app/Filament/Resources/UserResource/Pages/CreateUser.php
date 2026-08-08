<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Redirect ke halaman index setelah data tersimpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Fungsi ini seperti logika di Controller sebelum Model::create()
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Contoh: Set password default (misal: 12345678)
        // Atau ambil dari logika kustom Anda sendiri
        $data['password'] = Hash::make('basmalah');

        // Jika Anda ingin passwordnya diambil dari field lain, misal No WhatsApp:
        // $data['password'] = Hash::make($data['whatsapp']);

        return $data;
    }
}
