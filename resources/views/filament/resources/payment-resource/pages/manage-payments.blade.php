<x-filament-panels::page>
<style>
/* ── Tab Bar (absensi style) ── */
.ptab-bar{display:grid;grid-template-columns:1fr 1fr 1fr;background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:.75rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.dark .ptab-bar{background:#1e293b;border-color:#334155}
.ptab-btn{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.9rem .5rem;font-size:.8rem;font-weight:700;border:none;background:transparent;cursor:pointer;color:#94a3b8;border-bottom:3px solid transparent;transition:all .2s;letter-spacing:.01em}
.ptab-btn.active{color:#0ea5e9;border-bottom-color:#0ea5e9;background:rgba(14,165,233,.05)}
.dark .ptab-btn.active{color:#38bdf8;border-bottom-color:#38bdf8;background:rgba(56,189,248,.07)}
/* ── Dots ── */
.ptab-dots{display:flex;justify-content:center;gap:.45rem;margin-bottom:1.25rem}
.ptab-dot{width:.55rem;height:.55rem;border-radius:9999px;transition:all .3s}
.ptab-dot.active{background:#f59e0b;width:1.4rem}
.ptab-dot:not(.active){background:#cbd5e1}
.dark .ptab-dot:not(.active){background:#475569}
/* ── Slider ── */
.ptab-wrap{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.ptab-wrap::-webkit-scrollbar{display:none}
.ptab-panel{min-width:100%;scroll-snap-align:start;padding-bottom:2rem}
/* ── Cards ── */
.pc{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.1rem 1.2rem;margin-bottom:.65rem;transition:box-shadow .2s}
.pc:hover{box-shadow:0 4px 16px rgba(14,165,233,.12)}
.dark .pc{background:#1e293b;border-color:#334155}
.pc-title{font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:.2rem}
.dark .pc-title{color:#f1f5f9}
.pc-sub{font-size:.78rem;color:#64748b}
.dark .pc-sub{color:#94a3b8}
/* ── Badges ── */
.pb{display:inline-flex;align-items:center;padding:.15rem .55rem;border-radius:9999px;font-size:.7rem;font-weight:700}
.pb-green{background:#dcfce7;color:#166534}.pb-yellow{background:#fef9c3;color:#854d0e}
.pb-red{background:#fee2e2;color:#991b1b}.pb-blue{background:#dbeafe;color:#1e40af}
.pb-gray{background:#f1f5f9;color:#475569}.pb-violet{background:#ede9fe;color:#5b21b6}
/* ── Buttons ── */
.pbtn-p{background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;border:none;border-radius:.6rem;padding:.5rem 1rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:opacity .2s}
.pbtn-p:hover{opacity:.87}
.pbtn-e{background:rgba(245,158,11,.15);color:#b45309;border:none;border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:700;cursor:pointer}
.pbtn-d{background:rgba(239,68,68,.13);color:#dc2626;border:none;border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:700;cursor:pointer}
.pbtn-t{background:rgba(139,92,246,.13);color:#7c3aed;border:none;border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:700;cursor:pointer}
/* ── Search / Form ── */
.pinput{width:100%;padding:.5rem .8rem;border:1px solid #e2e8f0;border-radius:.65rem;font-size:.85rem;background:#f8fafc;color:#1e293b;transition:border .2s}
.pinput:focus{outline:none;border-color:#0ea5e9}
.dark .pinput{background:#0f172a;border-color:#334155;color:#e2e8f0}
.plbl{font-size:.78rem;font-weight:600;color:#475569;margin-bottom:.25rem;display:block}
.dark .plbl{color:#94a3b8}
/* ── Modal ── */
.pmodal-bg{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem}
.pmodal{background:#fff;border-radius:1.1rem;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.dark .pmodal{background:#1e293b}
.pmodal-title{font-size:1rem;font-weight:800;color:#1e293b;margin-bottom:1.25rem}
.dark .pmodal-title{color:#f1f5f9}
.pg2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.ptoggle{display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border-radius:.6rem;margin-bottom:.4rem;background:#f8fafc}
.dark .ptoggle{background:#0f172a}
.ptoggle input[type=checkbox]{width:1.1rem;height:1.1rem;accent-color:#0ea5e9;cursor:pointer}
.empty-state{text-align:center;padding:3rem 1rem;color:#94a3b8}
.code-chip{font-family:monospace;font-size:.9rem;font-weight:800;color:#6366f1;background:rgba(99,102,241,.1);padding:.15rem .55rem;border-radius:.4rem;letter-spacing:.06em}
@media(max-width:600px){.pg2{grid-template-columns:1fr}}
</style>

<div x-data="{
    tab: @entangle('payTab').live,
    go(i){
        const w=document.getElementById('ptab-wrap');
        if(w) w.scrollTo({left:i*w.offsetWidth,behavior:'smooth'});
        this.tab=i;
    }
}">

{{-- ── Tab Bar ── --}}
<div class="ptab-bar">
    <button type="button" class="ptab-btn" :class="tab===0?'active':''" @click="go(0)">
        💳 Payment
    </button>
    <button type="button" class="ptab-btn" :class="tab===1?'active':''" @click="go(1)">
        🎟️ Kupon
    </button>
    <button type="button" class="ptab-btn" :class="tab===2?'active':''" @click="go(2)">
        📜 Log
    </button>
</div>

{{-- ── Dots ── --}}
<div class="ptab-dots">
    <div class="ptab-dot" :class="tab===0?'active':''"></div>
    <div class="ptab-dot" :class="tab===1?'active':''"></div>
    <div class="ptab-dot" :class="tab===2?'active':''"></div>
</div>

{{-- ── Panels ── --}}
<div id="ptab-wrap" class="ptab-wrap"
     @scroll.passive="const w=$event.target;const i=Math.round(w.scrollLeft/w.offsetWidth);if(tab!==i){tab=i;$wire.set('payTab',i)}">

    {{-- ══ PANEL 0 — PAYMENT ══ --}}
    <div class="ptab-panel">
        <div style="display:flex;gap:.65rem;align-items:center;margin-bottom:1rem">
            <input wire:model.live.debounce.300ms="searchPayment" class="pinput" placeholder="🔍 Cari penagihan..." style="flex:1">
            <button type="button" class="pbtn-p" wire:click="openCreatePayment">+ Buat</button>
        </div>

        @forelse($this->payments as $p)
        <div class="pc">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem">
                <div style="flex:1">
                    <div class="pc-title">{{ $p->desc }}</div>
                    <div class="pc-sub">📅 {{ $p->start?->format('d M Y') }} — {{ $p->end?->format('d M Y') }}</div>
                    <div class="pc-sub" style="margin-top:.2rem">
                        💰 Dasar: <strong>Rp {{ number_format($p->amount_dasar,0,',','.') }}</strong>
                        &nbsp;|&nbsp;
                        Lanjutan: <strong>Rp {{ number_format($p->amount_lanjutan,0,',','.') }}</strong>
                    </div>
                    <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.4rem">
                        @if($p->va)   <span class="pb pb-blue">🏧 VA</span>   @endif
                        @if($p->qris) <span class="pb pb-violet">📱 QRIS</span> @endif
                        @if($p->cs)   <span class="pb pb-yellow">🏪 CS</span>  @endif
                        @php $sc=match($p->status){'active','PAID'=>'pb-green','PENDING'=>'pb-yellow','EXPIRED'=>'pb-red',default=>'pb-gray'}; @endphp
                        <span class="pb {{ $sc }}">{{ strtoupper($p->status) }}</span>
                    </div>
                    @if($p->send_reminder && $p->reminder_days_before)
                    <div class="pc-sub" style="margin-top:.3rem">🔔 Reminder H-{{ $p->reminder_days_before }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:.35rem;flex-shrink:0">
                    <button type="button" class="pbtn-e" wire:click="openEditPayment({{ $p->id }})">✏️</button>
                    <button type="button" class="pbtn-d" wire:click="deletePayment({{ $p->id }})"
                        wire:confirm="Hapus penagihan '{{ addslashes($p->desc) }}'?">🗑️</button>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div style="font-size:2.5rem;margin-bottom:.5rem">💳</div>
            <div style="font-weight:600">Belum ada penagihan</div>
        </div>
        @endforelse
    </div>

    {{-- ══ PANEL 1 — KUPON ══ --}}
    <div class="ptab-panel">
        <div style="display:flex;gap:.65rem;align-items:center;margin-bottom:1rem">
            <input wire:model.live.debounce.300ms="searchKupon" class="pinput" placeholder="🔍 Cari kupon..." style="flex:1">
            <button type="button" class="pbtn-p" wire:click="openCreateKupon">+ Buat</button>
        </div>

        @forelse($this->kupons as $k)
        <div class="pc">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem">
                        <span class="code-chip">{{ $k->code }}</span>
                        <span class="pc-title" style="margin-bottom:0">{{ $k->name }}</span>
                        <span class="pb {{ $k->is_active ? 'pb-green' : 'pb-red' }}">{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="pc-sub">💰 Potongan: <strong>Rp {{ number_format($k->amount,0,',','.') }}</strong></div>
                    <div class="pc-sub">📅 {{ $k->valid_from?->format('d M Y') }} — {{ $k->valid_until?->format('d M Y') }}</div>
                    @if($k->used_by)
                    <div style="font-size:.75rem;color:#16a34a;margin-top:.2rem">✅ {{ $k->used_by }} {{ $k->used_at ? '('.$k->used_at->format('d M Y H:i').')' : '' }}</div>
                    @else
                    <div style="font-size:.75rem;color:#94a3b8;margin-top:.2rem">⚡ Belum dipakai</div>
                    @endif
                </div>
                <div style="display:flex;gap:.35rem;flex-shrink:0;flex-direction:column;align-items:flex-end">
                    <button type="button" class="pbtn-t" wire:click="toggleKuponStatus({{ $k->id }})">{{ $k->is_active ? '🔒' : '🔓' }}</button>
                    <div style="display:flex;gap:.35rem">
                        <button type="button" class="pbtn-e" wire:click="openEditKupon({{ $k->id }})">✏️</button>
                        <button type="button" class="pbtn-d" wire:click="deleteKupon({{ $k->id }})" wire:confirm="Hapus kupon '{{ $k->code }}'?">🗑️</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div style="font-size:2.5rem;margin-bottom:.5rem">🎟️</div>
            <div style="font-weight:600">Belum ada kupon</div>
        </div>
        @endforelse
    </div>

    {{-- ══ PANEL 2 — LOG ══ --}}
    <div class="ptab-panel">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:.6rem;align-items:end;margin-bottom:1rem">
            <div>
                <label class="plbl">Nama</label>
                <input wire:model.live.debounce.400ms="searchLog" class="pinput" placeholder="Cari...">
            </div>
            <div>
                <label class="plbl">Dari</label>
                <input wire:model.live="logFrom" type="date" class="pinput">
            </div>
            <div>
                <label class="plbl">Sampai</label>
                <input wire:model.live="logUntil" type="date" class="pinput">
            </div>
            <button type="button" wire:click="resetLogFilter" class="pbtn-e" style="height:2.3rem">Reset</button>
        </div>

        @php $logData = $this->logs; @endphp
        <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.65rem">Total: <strong>{{ $logData['total'] }}</strong> transaksi</div>

        @forelse($logData['records'] as $log)
        <div class="pc">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem">
                <div>
                    <div class="pc-title">{{ $log->civitas?->name ?? $log->civitas_id }}</div>
                    <div class="pc-sub">{{ ucfirst($log->angkatan) }} &nbsp;|&nbsp; 💰 Rp {{ number_format($log->amount,0,',','.') }}</div>
                    <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.35rem">
                        @if($log->coupon_id)
                            <span class="pb pb-blue">🎟️ {{ $log->coupon?->code ?? '-' }}</span>
                            <span class="pb pb-green">-Rp {{ number_format($log->discount_amount,0,',','.') }}</span>
                        @else
                            <span class="pb pb-red">🎟️ Tidak Ada</span>
                            <span class="pb pb-red">Potongan: Tidak Ada</span>
                        @endif
                    </div>
                    <div class="pc-sub" style="margin-top:.3rem">{{ $log->payment?->desc ?? '-' }} · {{ $log->created_at?->format('d M Y H:i') }}</div>
                </div>
                <span class="pb {{ $log->status === 'PAID' ? 'pb-green' : 'pb-yellow' }}" style="flex-shrink:0">{{ $log->status }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div style="font-size:2.5rem;margin-bottom:.5rem">📜</div>
            <div style="font-weight:600">Belum ada log</div>
        </div>
        @endforelse

        @if($logData['pages'] > 1)
        <div style="display:flex;justify-content:center;align-items:center;gap:.75rem;margin-top:1rem">
            <button type="button" class="pbtn-p" wire:click="logPrev" @disabled($logData['currentPage'] <= 1)>◀</button>
            <span style="font-size:.85rem;font-weight:700;color:#64748b">{{ $logData['currentPage'] }} / {{ $logData['pages'] }}</span>
            <button type="button" class="pbtn-p" wire:click="logNext({{ $logData['pages'] }})" @disabled($logData['currentPage'] >= $logData['pages'])>▶</button>
        </div>
        @endif
    </div>

</div>{{-- end ptab-wrap --}}
</div>{{-- end x-data --}}

{{-- ══════════════ MODAL PAYMENT ══════════════ --}}
@if($showPaymentModal)
<div class="pmodal-bg" wire:click.self="$set('showPaymentModal',false)">
    <div class="pmodal">
        <div class="pmodal-title">{{ $editPaymentId ? '✏️ Edit Penagihan' : '+ Buat Penagihan' }}</div>

        <div style="margin-bottom:.9rem">
            <label class="plbl">Deskripsi *</label>
            <input wire:model="desc" class="pinput" placeholder="Contoh: SPP Bulan Juni 2026">
            @error('desc') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
        </div>

        <div class="pg2" style="margin-bottom:.9rem">
            <div>
                <label class="plbl">Periode Mulai *</label>
                <input wire:model="start" type="date" class="pinput">
                @error('start') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="plbl">Batas Akhir *</label>
                <input wire:model="end" type="date" class="pinput">
                @error('end') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="pg2" style="margin-bottom:.9rem">
            <div>
                <label class="plbl">Nominal Dasar (Rp) *</label>
                <input wire:model="amount_dasar" type="number" min="0" class="pinput">
                @error('amount_dasar') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="plbl">Nominal Lanjutan (Rp) *</label>
                <input wire:model="amount_lanjutan" type="number" min="0" class="pinput">
                @error('amount_lanjutan') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-bottom:.9rem">
            <label class="plbl">Metode Pembayaran</label>
            <div class="ptoggle"><span style="font-size:.85rem">🏧 Virtual Account</span><input type="checkbox" wire:model="va"></div>
            <div class="ptoggle"><span style="font-size:.85rem">📱 QRIS</span><input type="checkbox" wire:model="qris"></div>
            <div class="ptoggle"><span style="font-size:.85rem">🏪 Convenient Store</span><input type="checkbox" wire:model="cs"></div>
        </div>

        <div class="ptoggle" style="margin-bottom:.5rem">
            <span style="font-size:.85rem;font-weight:600">🔔 Kirim Reminder Email</span>
            <input type="checkbox" wire:model.live="send_reminder">
        </div>
        @if($send_reminder)
        <div style="margin-bottom:.9rem">
            <label class="plbl">H- sebelum batas akhir</label>
            <input wire:model="reminder_days_before" type="number" min="1" class="pinput" placeholder="3" style="max-width:120px">
        </div>
        @endif

        <div style="display:flex;gap:.65rem;justify-content:flex-end;margin-top:1.25rem">
            <button type="button" wire:click="$set('showPaymentModal',false)"
                style="padding:.5rem 1rem;border-radius:.65rem;border:1px solid #e2e8f0;background:transparent;cursor:pointer;font-size:.85rem;color:#64748b">Batal</button>
            <button type="button" class="pbtn-p" wire:click="savePayment" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="savePayment">{{ $editPaymentId ? 'Simpan' : 'Buat' }}</span>
                <span wire:loading wire:target="savePayment">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ══════════════ MODAL KUPON ══════════════ --}}
@if($showKuponModal)
<div class="pmodal-bg" wire:click.self="$set('showKuponModal',false)">
    <div class="pmodal">
        <div class="pmodal-title">{{ $editKuponId ? '✏️ Edit Kupon' : '+ Buat Kupon' }}</div>

        @if(!$editKuponId)
        <div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.75rem;padding:.7rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#6366f1;font-weight:600">
            ✨ Kode 6 karakter auto-generated saat disimpan
        </div>
        @endif

        <div style="margin-bottom:.9rem">
            <label class="plbl">Nama Kupon *</label>
            <input wire:model="coupon_name" class="pinput" placeholder="Contoh: Diskon Early Bird">
            @error('coupon_name') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:.9rem">
            <label class="plbl">Nilai Potongan (Rp) *</label>
            <input wire:model="coupon_amount" type="number" min="0" class="pinput">
            @error('coupon_amount') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
        </div>

        <div class="pg2" style="margin-bottom:.9rem">
            <div>
                <label class="plbl">Berlaku Mulai *</label>
                <input wire:model="coupon_valid_from" type="date" class="pinput">
                @error('coupon_valid_from') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="plbl">Berlaku Sampai *</label>
                <input wire:model="coupon_valid_until" type="date" class="pinput">
                @error('coupon_valid_until') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="ptoggle" style="margin-bottom:1rem">
            <span style="font-size:.85rem;font-weight:600">Aktifkan Kupon</span>
            <input type="checkbox" wire:model="coupon_is_active">
        </div>

        <div style="display:flex;gap:.65rem;justify-content:flex-end">
            <button type="button" wire:click="$set('showKuponModal',false)"
                style="padding:.5rem 1rem;border-radius:.65rem;border:1px solid #e2e8f0;background:transparent;cursor:pointer;font-size:.85rem;color:#64748b">Batal</button>
            <button type="button" class="pbtn-p" wire:click="saveKupon" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveKupon">{{ $editKuponId ? 'Simpan' : 'Buat' }}</span>
                <span wire:loading wire:target="saveKupon">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
