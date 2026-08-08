<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\DiscountCoupon;
use App\Models\Payment;
use App\Models\PaymentLog;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManagePayments extends ManageRecords
{
    protected static string $resource = PaymentResource::class;

    // ─── Livewire State ──────────────────────────────────────────────────────
    public int    $payTab        = 0;    // 0=Payment, 1=Kupon, 2=Log
    public string $searchPayment = '';
    public string $searchKupon   = '';
    public string $searchLog     = '';
    public string $logFrom       = '';
    public string $logUntil      = '';
    public int    $logPage       = 1;
    public int    $perPage       = 10;

    // ─── Modal states ────────────────────────────────────────────────────────
    public bool  $showPaymentModal = false;
    public bool  $showKuponModal   = false;
    public ?int  $editPaymentId    = null;
    public ?int  $editKuponId      = null;

    // ─── Payment form ────────────────────────────────────────────────────────
    public string $desc                 = '';
    public string $start                = '';
    public string $end                  = '';
    public bool   $va                   = false;
    public bool   $qris                 = false;
    public bool   $cs                   = false;
    public string $nominal              = '0';
    public bool   $send_reminder        = false;
    public string $reminder_days_before = '';
    public string $level                = '';

    // ─── Kupon form ──────────────────────────────────────────────────────────
    public string $coupon_name        = '';
    public string $coupon_amount      = '0';
    public string $coupon_valid_from  = '';
    public string $coupon_valid_until = '';
    public bool   $coupon_is_active   = true;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // ─── Tab switching ───────────────────────────────────────────────────────
    public function setTab(int $tab): void
    {
        $this->payTab  = $tab;
        $this->logPage   = 1;
    }

    // ─── Payment CRUD ────────────────────────────────────────────────────────
    public function openCreatePayment(): void
    {
        $this->resetPaymentForm();
        $this->editPaymentId     = null;
        $this->showPaymentModal  = true;
    }

    public function openEditPayment(int $id): void
    {
        $record = Payment::findOrFail($id);
        $this->editPaymentId         = $id;
        $this->desc                  = $record->desc ?? '';
        $this->start                 = $record->start?->format('Y-m-d') ?? '';
        $this->end                   = $record->end?->format('Y-m-d') ?? '';
        $this->level                 = $record->level ?? '';
        $this->va                    = (bool) $record->va;
        $this->qris                  = (bool) $record->qris;
        $this->cs                    = (bool) $record->cs;
        $this->nominal               = (string) ($record->level === 'semester_3' ? $record->semester_3 : $record->semester_2);
        $this->send_reminder         = (bool) $record->send_reminder;
        $this->reminder_days_before  = (string) ($record->reminder_days_before ?? '');
        $this->showPaymentModal      = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'desc'           => 'required|string|max:255',
            'start'          => 'required|date',
            'end'            => 'required|date|after_or_equal:start',
            'nominal'        => 'required|numeric|min:0',
            'level'          => 'required|in:semester_2,semester_3',
        ], [
            'desc.required'           => 'Deskripsi wajib diisi.',
            'start.required'          => 'Periode mulai wajib diisi.',
            'end.required'            => 'Batas akhir wajib diisi.',
            'end.after_or_equal'      => 'Batas akhir harus setelah periode mulai.',
            'nominal.required'        => 'Nominal wajib diisi.',
            'level.required'          => 'Level wajib dipilih.',
            'level.in'                => 'Level tidak valid.',
        ]);

        $data = [
            'desc'                  => $this->desc,
            'start'                 => $this->start,
            'end'                   => $this->end,
            'level'                 => $this->level,
            'va'                    => $this->va,
            'qris'                  => $this->qris,
            'cs'                    => $this->cs,
            'send_reminder'         => $this->send_reminder,
            'reminder_days_before'  => $this->send_reminder ? ($this->reminder_days_before ?: null) : null,
            'created_by'            => Auth::id(),
            'status'                => 'active',
        ];

        // Nominal dinamis mengikuti level terpilih
        if ($this->level === 'semester_3') {
            $data['semester_3'] = $this->nominal;
            $data['semester_2'] = 0;
        } else {
            $data['semester_2'] = $this->nominal;
            $data['semester_3'] = 0;
        }

        if ($this->editPaymentId) {
            Payment::findOrFail($this->editPaymentId)->update($data);
            Notification::make()->title('Penagihan diperbarui')->success()->send();
        } else {
            Payment::create($data);
            Notification::make()->title('Penagihan berhasil dibuat')->success()->send();
        }

        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function deletePayment(int $id): void
    {
        Payment::findOrFail($id)->delete();
        Notification::make()->title('Penagihan dihapus')->warning()->send();
    }

    private function resetPaymentForm(): void
    {
        $this->desc                 = '';
        $this->start                = '';
        $this->end                  = '';
        $this->level                = '';
        $this->va                   = false;
        $this->qris                 = false;
        $this->cs                   = false;
        $this->nominal              = '0';
        $this->send_reminder        = false;
        $this->reminder_days_before = '';
        $this->resetErrorBag();
    }

    // ─── Kupon CRUD ──────────────────────────────────────────────────────────
    public function openCreateKupon(): void
    {
        $this->resetKuponForm();
        $this->editKuponId    = null;
        $this->showKuponModal = true;
    }

    public function openEditKupon(int $id): void
    {
        $record = DiscountCoupon::findOrFail($id);
        $this->editKuponId         = $id;
        $this->coupon_name         = $record->name;
        $this->coupon_amount       = (string) $record->amount;
        $this->coupon_valid_from   = $record->valid_from?->format('Y-m-d') ?? '';
        $this->coupon_valid_until  = $record->valid_until?->format('Y-m-d') ?? '';
        $this->coupon_is_active    = (bool) $record->is_active;
        $this->showKuponModal      = true;
    }

    public function saveKupon(): void
    {
        $this->validate([
            'coupon_name'        => 'required|string|max:255',
            'coupon_amount'      => 'required|numeric|min:0',
            'coupon_valid_from'  => 'required|date',
            'coupon_valid_until' => 'required|date|after_or_equal:coupon_valid_from',
        ], [
            'coupon_name.required'        => 'Nama kupon wajib diisi.',
            'coupon_amount.required'      => 'Nilai potongan wajib diisi.',
            'coupon_valid_from.required'  => 'Tanggal mulai berlaku wajib diisi.',
            'coupon_valid_until.required' => 'Tanggal akhir berlaku wajib diisi.',
            'coupon_valid_until.after_or_equal' => 'Tanggal akhir harus setelah tanggal mulai.',
        ]);

        $data = [
            'name'        => $this->coupon_name,
            'amount'      => $this->coupon_amount,
            'valid_from'  => $this->coupon_valid_from,
            'valid_until' => $this->coupon_valid_until,
            'is_active'   => $this->coupon_is_active,
        ];

        if ($this->editKuponId) {
            DiscountCoupon::findOrFail($this->editKuponId)->update($data);
            Notification::make()->title('Kupon diperbarui')->success()->send();
        } else {
            DiscountCoupon::create($data); // code auto-generated in model boot
            Notification::make()->title('Kupon berhasil dibuat')->success()->send();
        }

        $this->showKuponModal = false;
        $this->resetKuponForm();
    }

    public function deleteKupon(int $id): void
    {
        DiscountCoupon::findOrFail($id)->delete();
        Notification::make()->title('Kupon dihapus')->warning()->send();
    }

    public function toggleKuponStatus(int $id): void
    {
        $coupon = DiscountCoupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        Notification::make()
            ->title($coupon->is_active ? 'Kupon diaktifkan' : 'Kupon dinonaktifkan')
            ->success()->send();
    }

    private function resetKuponForm(): void
    {
        $this->coupon_name        = '';
        $this->coupon_amount      = '0';
        $this->coupon_valid_from  = '';
        $this->coupon_valid_until = '';
        $this->coupon_is_active   = true;
        $this->resetErrorBag();
    }

    // ─── Log pagination ──────────────────────────────────────────────────────
    public function logPrev(): void
    {
        if ($this->logPage > 1) $this->logPage--;
    }

    public function logNext(int $totalPages): void
    {
        if ($this->logPage < $totalPages) $this->logPage++;
    }

    public function resetLogFilter(): void
    {
        $this->searchLog = '';
        $this->logFrom   = '';
        $this->logUntil  = '';
        $this->logPage   = 1;
    }

    // ─── Computed data ───────────────────────────────────────────────────────
    public function getPaymentsProperty()
    {
        return Payment::query()
            ->when($this->searchPayment, fn ($q) => $q->where('desc', 'like', "%{$this->searchPayment}%"))
            ->orderByDesc('created_at')
            ->get();
    }

    public function getKuponsProperty()
    {
        return DiscountCoupon::query()
            ->when($this->searchKupon, fn ($q) => $q->where('name', 'like', "%{$this->searchKupon}%")
                ->orWhere('code', 'like', "%{$this->searchKupon}%"))
            ->orderByDesc('created_at')
            ->get();
    }

    public function getLogsProperty(): array
    {
        $query = PaymentLog::with(['payment', 'coupon', 'civitas'])
            ->when($this->searchLog, fn ($q) => $q->where('civitas_id', 'like', "%{$this->searchLog}%"))
            ->when($this->logFrom,   fn ($q) => $q->whereDate('created_at', '>=', $this->logFrom))
            ->when($this->logUntil,  fn ($q) => $q->whereDate('created_at', '<=', $this->logUntil))
            ->orderByDesc('created_at');

        $total   = $query->count();
        $pages   = (int) ceil($total / $this->perPage);
        $records = $query->skip(($this->logPage - 1) * $this->perPage)->take($this->perPage)->get();

        return [
            'records'     => $records,
            'total'       => $total,
            'pages'       => max(1, $pages),
            'currentPage' => $this->logPage,
        ];
    }

    protected static string $view = 'filament.resources.payment-resource.pages.manage-payments';
}
