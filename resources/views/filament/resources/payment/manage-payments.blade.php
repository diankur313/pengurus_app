<x-filament-panels::page>
<style>
.pay-wrap{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;gap:0;scrollbar-width:none}
.pay-wrap::-webkit-scrollbar{display:none}
.pay-panel{min-width:100%;scroll-snap-align:start;padding:0 0 2rem 0}
.tab-pills{display:flex;gap:8px;margin-bottom:1.5rem;background:rgba(255,255,255,.06);padding:6px;border-radius:12px}
.tab-pill{flex:1;padding:8px 0;border:none;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s;background:transparent;color:#94a3b8}
.tab-pill.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 12px rgba(99,102,241,.4)}
.pay-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:1.1rem 1.25rem;margin-bottom:.75rem;transition:box-shadow .2s}
.pay-card:hover{box-shadow:0 4px 20px rgba(99,102,241,.15)}
.pay-card-title{font-size:1rem;font-weight:700;color:#e2e8f0;margin-bottom:.25rem}
.pay-card-sub{font-size:.8rem;color:#94a3b8}
.badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:.72rem;font-weight:600}
.badge-green{background:rgba(34,197,94,.15);color:#4ade80}
.badge-red{background:rgba(239,68,68,.15);color:#f87171}
.badge-yellow{background:rgba(250,204,21,.15);color:#facc15}
.badge-blue{background:rgba(99,102,241,.15);color:#818cf8}
.badge-gray{background:rgba(148,163,184,.15);color:#94a3b8}
.action-btn{border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:.78rem;font-weight:600;transition:all .15s}
.btn-edit{background:rgba(99,102,241,.15);color:#818cf8}.btn-edit:hover{background:rgba(99,102,241,.3)}
.btn-del{background:rgba(239,68,68,.12);color:#f87171}.btn-del:hover{background:rgba(239,68,68,.25)}
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem}
.modal-box{background:#1e1e2e;border:1px solid rgba(255,255,255,.1);border-radius:18px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:1.75rem}
.modal-title{font-size:1.1rem;font-weight:700;color:#e2e8f0;margin-bottom:1.25rem}
.form-group{margin-bottom:1rem}
.form-label{display:block;font-size:.8rem;font-weight:600;color:#94a3b8;margin-bottom:.4rem}
.form-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:.55rem .85rem;color:#e2e8f0;font-size:.88rem;transition:border .2s}
.form-input:focus{outline:none;border-color:#6366f1}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;background:rgba(255,255,255,.04);border-radius:9px;margin-bottom:.5rem}
.toggle-label{font-size:.85rem;color:#cbd5e1;font-weight:500}
.search-bar{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.55rem 1rem;color:#e2e8f0;font-size:.88rem;margin-bottom:1rem}
.search-bar:focus{outline:none;border-color:#6366f1}
.btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;padding:.6rem 1.25rem;font-weight:600;cursor:pointer;transition:opacity .2s;font-size:.88rem}
.btn-primary:hover{opacity:.88}
.btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:none;border-radius:10px;padding:.6rem 1.25rem;font-weight:600;cursor:pointer;font-size:.88rem}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.code-badge{font-family:monospace;font-size:.95rem;font-weight:700;color:#a78bfa;background:rgba(167,139,250,.12);padding:2px 10px;border-radius:6px;letter-spacing:.08em}
.filter-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.6rem;margin-bottom:1rem}
@media(max-width:600px){.filter-row{grid-template-columns:1fr}.grid-2{grid-template-columns:1fr}}
.empty-state{text-align:center;padding:3rem 1rem;color:#475569}
.coupon-used{font-size:.75rem;color:#4ade80;margin-top:.25rem}
.coupon-unused{font-size:.75rem;color:#94a3b8;margin-top:.25rem}
</style>

<div x-data="{
    tab: @entangle('activeTab'),
    scrollToTab(idx){
        const w = document.getElementById('pay-scroll-wrap');
        if(!w) return;
        w.scrollTo({left: idx * w.offsetWidth, behavior:'smooth'});
        this.tab = idx;
        $wire.set('activeTab', idx);
    }
}">

    {{-- Tab Pills --}}
    <div class="tab-pills">
        @foreach(['💳 Payment','🎟️ Kupon','📜 Log'] as $i => $label)
            <button class="tab-pill" :class="tab === {{ $i }} ? 'active' : ''"
                @click="scrollToTab({{ $i }})" type="button">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Slider --}}
    <div id="pay-scroll-wrap" class="pay-wrap"
         @scroll.passive="
            const w=$event.target;
            const idx=Math.round(w.scrollLeft/w.offsetWidth);
            if(tab!==idx){tab=idx;$wire.set('activeTab',idx);}
         ">

        {{-- ╔══════════════╗ --}}
        {{-- ║  TAB PAYMENT  ║ --}}
        {{-- ╚══════════════╝ --}}
        <div class="pay-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <input wire:model.live.debounce.300="paymentSearch" class="search-bar" style="margin-bottom:0;flex:1;margin-right:.75rem"
                    placeholder="🔍 Cari payment...">
                <button class="btn-primary" wire:click="openCreatePayment" type="button">+ Buat Payment</button>
            </div>

            @forelse($this->payments as $p)
            <div class="pay-card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <div class="pay-card-title">{{ $p->desc }}</div>
                        <div class="pay-card-sub">📅 {{ $p->start?->format('d M Y') }} – {{ $p->end?->format('d M Y') }}</div>
                        <div class="pay-card-sub" style="margin-top:.3rem">
                            💰 Dasar: <strong style="color:#e2e8f0">Rp {{ number_format($p->amount_dasar,0,',','.') }}</strong>
                            &nbsp;|&nbsp;
                            Lanjutan: <strong style="color:#e2e8f0">Rp {{ number_format($p->amount_lanjutan,0,',','.') }}</strong>
                        </div>
                        <div style="margin-top:.4rem;display:flex;gap:.4rem;flex-wrap:wrap">
                            @if($p->va)<span class="badge badge-blue">🏧 VA</span>@endif
                            @if($p->qris)<span class="badge badge-green">📱 QRIS</span>@endif
                            @if($p->cs)<span class="badge badge-yellow">🏪 CS</span>@endif
                            <span class="badge {{ in_array($p->status,['active','PAID']) ? 'badge-green' : (in_array($p->status,['PENDING']) ? 'badge-yellow' : 'badge-gray') }}">
                                {{ strtoupper($p->status) }}
                            </span>
                        </div>
                    </div>
                    <div style="display:flex;gap:.4rem;flex-shrink:0;margin-left:.75rem">
                        <button class="action-btn btn-edit" wire:click="openEditPayment({{ $p->id }})" type="button">✏️</button>
                        <button class="action-btn btn-del" wire:click="deletePayment({{ $p->id }})"
                            wire:confirm="Hapus payment ini?" type="button">🗑️</button>
                    </div>
                </div>
                @if($p->send_reminder && $p->reminder_days_before)
                    <div class="pay-card-sub" style="margin-top:.35rem">🔔 Reminder {{ $p->reminder_days_before }} hari sebelum batas akhir</div>
                @endif
            </div>
            @empty
            <div class="empty-state">Belum ada data payment.</div>
            @endforelse

            <div style="margin-top:1rem">{{ $this->payments->links() }}</div>
        </div>

        {{-- ╔════════════╗ --}}
        {{-- ║  TAB KUPON  ║ --}}
        {{-- ╚════════════╝ --}}
        <div class="pay-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <input wire:model.live.debounce.300="couponSearch" class="search-bar" style="margin-bottom:0;flex:1;margin-right:.75rem"
                    placeholder="🔍 Cari nama / kode kupon...">
                <button class="btn-primary" wire:click="openCreateCoupon" type="button">+ Buat Kupon</button>
            </div>

            @forelse($this->coupons as $c)
            <div class="pay-card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div style="flex:1">
                        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.25rem">
                            <span>🎟️</span>
                            <span class="code-badge">{{ $c->code }}</span>
                            <span class="pay-card-title" style="margin-bottom:0">{{ $c->name }}</span>
                        </div>
                        <div class="pay-card-sub">💰 Potongan: <strong style="color:#e2e8f0">Rp {{ number_format($c->amount,0,',','.') }}</strong></div>
                        <div class="pay-card-sub">📅 {{ $c->valid_from?->format('d M Y') }} – {{ $c->valid_until?->format('d M Y') }}</div>
                        @if($c->used_by)
                            <div class="coupon-used">✅ Dipakai: {{ $c->used_by }} {{ $c->used_at ? '(' . $c->used_at->format('d M Y H:i') . ')' : '' }}</div>
                        @else
                            <div class="coupon-unused">⚡ Belum dipakai</div>
                        @endif
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.4rem;flex-shrink:0;margin-left:.75rem">
                        <span class="badge {{ $c->is_active ? 'badge-green' : 'badge-gray' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <div style="display:flex;gap:.4rem">
                            @if(!$c->used_by)
                                <button class="action-btn btn-edit" wire:click="openEditCoupon({{ $c->id }})" type="button">✏️</button>
                            @endif
                            <button class="action-btn btn-del" wire:click="deleteCoupon({{ $c->id }})"
                                wire:confirm="Hapus kupon ini?" type="button">🗑️</button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">Belum ada kupon.</div>
            @endforelse

            <div style="margin-top:1rem">{{ $this->coupons->links() }}</div>
        </div>

        {{-- ╔══════════════╗ --}}
        {{-- ║  TAB LOG      ║ --}}
        {{-- ╚══════════════╝ --}}
        <div class="pay-panel">
            <div class="filter-row">
                <input wire:model.live.debounce.400="logSearchName" class="search-bar" style="margin-bottom:0"
                    placeholder="🔍 Cari nama...">
                <input type="date" wire:model.live="logDateFrom" class="form-input" title="Dari tanggal">
                <input type="date" wire:model.live="logDateUntil" class="form-input" title="Sampai tanggal">
            </div>

            @forelse($this->logs as $log)
            @php $civitasName = $this->getCivitasName($log->civitas_id); @endphp
            <div class="pay-card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <div class="pay-card-title">{{ $civitasName }}</div>
                        <div class="pay-card-sub">{{ ucfirst($log->angkatan) }} &nbsp;|&nbsp; 💰 Rp {{ number_format($log->amount,0,',','.') }}</div>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.4rem">
                            {{-- Kupon --}}
                            @if($log->coupon_id)
                                <span class="badge badge-blue">🎟️ {{ $log->coupon?->code ?? $log->coupon_id }}</span>
                            @else
                                <span class="badge badge-red">🎟️ Tidak Ada</span>
                            @endif
                            {{-- Potongan --}}
                            @if($log->discount_amount > 0)
                                <span class="badge badge-green">Potongan: Rp {{ number_format($log->discount_amount,0,',','.') }}</span>
                            @else
                                <span class="badge badge-red">Potongan: Tidak Ada</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $log->status === 'PAID' ? 'badge-green' : 'badge-yellow' }}" style="flex-shrink:0;margin-left:.75rem">
                        {{ $log->status }}
                    </span>
                </div>
                <div class="pay-card-sub" style="margin-top:.35rem">{{ $log->payment?->desc ?? '-' }} &nbsp;·&nbsp; {{ $log->created_at?->format('d M Y H:i') }}</div>
            </div>
            @empty
            <div class="empty-state">Belum ada data log pembayaran.</div>
            @endforelse

            <div style="margin-top:1rem">{{ $this->logs->links() }}</div>
        </div>

    </div>{{-- end .pay-wrap --}}
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- MODAL: Payment Form                     --}}
{{-- ═══════════════════════════════════════ --}}
@if($showPaymentModal)
<div class="modal-bg" wire:click.self="$set('showPaymentModal',false)">
    <div class="modal-box">
        <div class="modal-title">{{ $editPaymentId ? '✏️ Edit Payment' : '+ Buat Payment' }}</div>

        <div class="form-group">
            <label class="form-label">Deskripsi *</label>
            <input class="form-input" wire:model="paymentForm.desc" placeholder="Contoh: SPP Bulan Juni 2026">
            @error('paymentForm.desc')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Mulai *</label>
                <input type="date" class="form-input" wire:model="paymentForm.start">
                @error('paymentForm.start')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Batas Akhir *</label>
                <input type="date" class="form-input" wire:model="paymentForm.end">
                @error('paymentForm.end')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Nominal Ang. Dasar (Rp) *</label>
                <input type="number" class="form-input" wire:model="paymentForm.amount_dasar" min="0" step="1000">
                @error('paymentForm.amount_dasar')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Ang. Lanjutan (Rp) *</label>
                <input type="number" class="form-input" wire:model="paymentForm.amount_lanjutan" min="0" step="1000">
                @error('paymentForm.amount_lanjutan')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
        </div>

        <p class="form-label" style="margin-bottom:.5rem">Metode Pembayaran</p>
        <div class="toggle-row">
            <span class="toggle-label">🏧 Virtual Account</span>
            <input type="checkbox" wire:model="paymentForm.va">
        </div>
        <div class="toggle-row">
            <span class="toggle-label">📱 QRIS</span>
            <input type="checkbox" wire:model="paymentForm.qris">
        </div>
        <div class="toggle-row" style="margin-bottom:1rem">
            <span class="toggle-label">🏪 Convenient Store</span>
            <input type="checkbox" wire:model="paymentForm.cs">
        </div>

        <div class="toggle-row">
            <span class="toggle-label">🔔 Kirim Reminder Email</span>
            <input type="checkbox" wire:model.live="paymentForm.send_reminder">
        </div>
        @if($paymentForm['send_reminder'])
        <div class="form-group" style="margin-top:.75rem">
            <label class="form-label">Reminder berapa hari sebelum batas akhir?</label>
            <input type="number" class="form-input" wire:model="paymentForm.reminder_days_before" min="1" placeholder="Contoh: 3">
            @error('paymentForm.reminder_days_before')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
        </div>
        @endif

        <div class="form-group" style="margin-top:.75rem">
            <label class="form-label">Status *</label>
            <select class="form-input" wire:model="paymentForm.status">
                <option value="active">Active</option>
                <option value="closed">Closed</option>
            </select>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem">
            <button class="btn-ghost" wire:click="$set('showPaymentModal',false)" type="button">Batal</button>
            <button class="btn-primary" wire:click="savePayment" type="button" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="savePayment">Simpan</span>
                <span wire:loading wire:target="savePayment">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════ --}}
{{-- MODAL: Coupon Form                      --}}
{{-- ═══════════════════════════════════════ --}}
@if($showCouponModal)
<div class="modal-bg" wire:click.self="$set('showCouponModal',false)">
    <div class="modal-box">
        <div class="modal-title">{{ $editCouponId ? '✏️ Edit Kupon' : '+ Buat Kupon' }}</div>

        @if($editCouponId)
        <div style="background:rgba(167,139,250,.1);border:1px solid rgba(167,139,250,.2);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem">
            <span style="font-size:.8rem;color:#94a3b8">Kode Kupon:</span>
            @php $editCode = \App\Models\DiscountCoupon::find($editCouponId)?->code; @endphp
            <span class="code-badge" style="margin-left:.5rem">{{ $editCode }}</span>
        </div>
        @else
        <div style="background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.15);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#4ade80">
            ✨ Kode 6 karakter akan otomatis di-generate saat disimpan.
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Nama Kupon *</label>
            <input class="form-input" wire:model="couponForm.name" placeholder="Contoh: Diskon Early Bird">
            @error('couponForm.name')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Nilai Potongan (Rp) *</label>
            <input type="number" class="form-input" wire:model="couponForm.amount" min="0" step="1000" placeholder="Contoh: 100000">
            @error('couponForm.amount')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Berlaku Dari *</label>
                <input type="date" class="form-input" wire:model="couponForm.valid_from">
                @error('couponForm.valid_from')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Berlaku Sampai *</label>
                <input type="date" class="form-input" wire:model="couponForm.valid_until">
                @error('couponForm.valid_until')<p style="color:#f87171;font-size:.75rem;margin-top:.2rem">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="toggle-row">
            <span class="toggle-label">Status Aktif</span>
            <input type="checkbox" wire:model="couponForm.is_active">
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem">
            <button class="btn-ghost" wire:click="$set('showCouponModal',false)" type="button">Batal</button>
            <button class="btn-primary" wire:click="saveCoupon" type="button" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveCoupon">Simpan</span>
                <span wire:loading wire:target="saveCoupon">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
