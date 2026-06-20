<?php

namespace App\Filament\Resources\PpabRegistrationResource\Pages;

use App\Filament\Resources\PpabRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPpabRegistrations extends ListRecords
{
    protected static string $resource = PpabRegistrationResource::class;
    
    protected static string $view = 'filament.resources.ppab-registration-resource.pages.manage-registrations';

    // ─── Livewire State ──────────────────────────────────────────────────────
    public int $payTab = 0; // 0=Registrasi, 1=Voucher
    public string $searchVoucher = '';

    // ─── Modal & Form states ──────────────────────────────────────────────────
    public bool $showVoucherModal = false;
    public ?int $editVoucherId = null;
    public string $voucherDiscount = '0';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => $this->payTab === 0), // Only show create registration on tab 0
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PpabRegistrationResource\Widgets\PpabRegistrationStatsWidget::class,
        ];
    }

    // ─── Tab switching ───────────────────────────────────────────────────────
    public function setTab(int $tab): void
    {
        $this->payTab = $tab;
    }

    // ─── Voucher CRUD ────────────────────────────────────────────────────────
    public function openCreateVoucher(): void
    {
        $this->voucherDiscount = '0';
        $this->editVoucherId = null;
        $this->showVoucherModal = true;
        $this->resetErrorBag();
    }

    public function saveVoucher(): void
    {
        $this->validate([
            'voucherDiscount' => 'required|numeric|min:1',
        ], [
            'voucherDiscount.required' => 'Potongan harga wajib diisi.',
            'voucherDiscount.min' => 'Potongan harga minimal 1.',
        ]);

        $data = [
            'discount' => $this->voucherDiscount,
        ];

        if ($this->editVoucherId) {
            \App\Models\PpabVoucher::findOrFail($this->editVoucherId)->update($data);
            \Filament\Notifications\Notification::make()->title('Voucher diperbarui')->success()->send();
        } else {
            // Generate 6 unique alphanumeric string for code
            do {
                $code = strtoupper(\Illuminate\Support\Str::random(6));
            } while (\App\Models\PpabVoucher::where('code', $code)->exists());

            $data['code'] = $code;
            \App\Models\PpabVoucher::create($data);
            \Filament\Notifications\Notification::make()->title('Voucher berhasil dibuat')->success()->send();
        }

        $this->showVoucherModal = false;
        $this->voucherDiscount = '0';
    }

    public function deleteVoucher(int $id): void
    {
        \App\Models\PpabVoucher::findOrFail($id)->delete();
        \Filament\Notifications\Notification::make()->title('Voucher dihapus')->warning()->send();
    }

    // ─── Computed data ───────────────────────────────────────────────────────
    public function getVouchersProperty()
    {
        return \App\Models\PpabVoucher::with('usedBy')
            ->when($this->searchVoucher, fn ($q) => $q->where('code', 'like', "%{$this->searchVoucher}%"))
            ->orderByDesc('created_at')
            ->get();
    }
}
