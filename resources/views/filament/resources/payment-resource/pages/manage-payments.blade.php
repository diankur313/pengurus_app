<x-filament-panels::page>
<style>
/* ── Tab Bar (absensi style) ── */
.ptab-bar{display:grid;grid-template-columns:1fr 1fr 1fr;background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,0.8);overflow:hidden;margin-bottom:1rem;box-shadow:0 4px 15px rgba(0,0,0,.03)}
.dark .ptab-bar{background:#1e293b;border-color:#334155;box-shadow:0 4px 15px rgba(0,0,0,.15)}
.ptab-btn{display:flex;align-items:center;justify-content:center;gap:.4rem;padding:1rem .5rem;font-size:.85rem;font-weight:700;border:none;background:transparent;cursor:pointer;color:#94a3b8;border-bottom:3px solid transparent;transition:all .3s;letter-spacing:.01em}
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
.pc{background:#fff;border:1px solid rgba(226,232,240,0.8);border-radius:16px;padding:1.35rem 1.4rem;margin-bottom:.85rem;transition:all .3s cubic-bezier(.4,0,.2,1);box-shadow:0 2px 10px rgba(0,0,0,.02);position:relative;overflow:hidden}
.pc::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:linear-gradient(180deg,#0ea5e9,#6366f1);opacity:0;transition:opacity .3s ease}
.pc:hover{box-shadow:0 10px 25px -5px rgba(14,165,233,.12),0 8px 10px -6px rgba(14,165,233,.08);transform:translateY(-2px);border-color:rgba(14,165,233,.3)}
.pc:hover::before{opacity:1}
.dark .pc{background:linear-gradient(145deg,#1e293b,#0f172a);border-color:rgba(51,65,85,.6);box-shadow:0 4px 12px rgba(0,0,0,.2)}
.dark .pc:hover{box-shadow:0 10px 25px -5px rgba(56,189,248,.15);border-color:rgba(56,189,248,.3)}
.pc-title{font-size:1.05rem;font-weight:800;color:#0f172a;margin-bottom:.35rem;letter-spacing:-.01em}
.dark .pc-title{color:#f8fafc}
.pc-sub{font-size:.85rem;color:#64748b;margin-bottom:.25rem}
.dark .pc-sub{color:#94a3b8}
/* ── Badges ── */
.pb{display:inline-flex;align-items:center;padding:.25rem .65rem;border-radius:9999px;font-size:.75rem;font-weight:700;letter-spacing:.02em;border:1px solid transparent}
.pb-green{background:rgba(34,197,94,.1);color:#15803d;border-color:rgba(34,197,94,.2)}
.pb-yellow{background:rgba(234,179,8,.1);color:#a16207;border-color:rgba(234,179,8,.2)}
.pb-red{background:rgba(239,68,68,.1);color:#b91c1c;border-color:rgba(239,68,68,.2)}
.pb-blue{background:rgba(59,130,246,.1);color:#1d4ed8;border-color:rgba(59,130,246,.2)}
.pb-gray{background:rgba(100,116,139,.1);color:#475569;border-color:rgba(100,116,139,.2)}
.pb-violet{background:rgba(139,92,246,.1);color:#6d28d9;border-color:rgba(139,92,246,.2)}
.dark .pb-green{color:#4ade80;border-color:rgba(74,222,128,.2)}
.dark .pb-yellow{color:#facc15;border-color:rgba(250,204,21,.2)}
.dark .pb-red{color:#f87171;border-color:rgba(248,113,113,.2)}
.dark .pb-blue{color:#60a5fa;border-color:rgba(96,165,250,.2)}
.dark .pb-gray{color:#cbd5e1;border-color:rgba(203,213,225,.2)}
.dark .pb-violet{color:#a78bfa;border-color:rgba(167,139,250,.2)}
/* ── Buttons ── */
.pbtn-p{background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;border:none;border-radius:.6rem;padding:.5rem 1.25rem;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(14,165,233,.25)}
.pbtn-p:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(14,165,233,.35)}
.pbtn-p:active{transform:translateY(0);box-shadow:0 2px 8px rgba(14,165,233,.2)}
.pbtn-e,.pbtn-d,.pbtn-t{width:2.2rem;height:2.2rem;display:flex;align-items:center;justify-content:center;border-radius:.6rem;transition:all .2s;font-size:1rem;border:none;cursor:pointer}
.pbtn-e{background:rgba(245,158,11,.1);color:#d97706}
.pbtn-e:hover{background:#f59e0b;color:#fff;transform:scale(1.1)}
.pbtn-d{background:rgba(239,68,68,.1);color:#dc2626}
.pbtn-d:hover{background:#ef4444;color:#fff;transform:scale(1.1)}
.pbtn-t{background:rgba(139,92,246,.1);color:#7c3aed}
.pbtn-t:hover{background:#8b5cf6;color:#fff;transform:scale(1.1)}
/* ── Search / Form ── */
.pinput{width:100%;padding:.65rem 1rem;border:1px solid #e2e8f0;border-radius:.85rem;font-size:.9rem;background:#f8fafc;color:#1e293b;transition:all .2s;box-shadow:inset 0 2px 4px rgba(0,0,0,0.01)}
.pinput:focus{outline:none;border-color:#0ea5e9;background:#fff;box-shadow:0 0 0 4px rgba(14,165,233,0.15)}
.dark .pinput{background:#0f172a;border-color:#334155;color:#e2e8f0;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2)}
.dark .pinput:focus{background:#1e293b;border-color:#38bdf8;box-shadow:0 0 0 4px rgba(56,189,248,0.15)}
select.pinput{-webkit-appearance:none;-moz-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;background-size:1rem;padding-right:2.5rem}
.dark select.pinput{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")}
.plbl{font-size:.8rem;font-weight:700;color:#334155;margin-bottom:.35rem;display:block;letter-spacing:0.01em}
.dark .plbl{color:#cbd5e1}
/* ── Modal ── */
.pmodal-bg{position:fixed;inset:0;background:rgba(15,23,42,.65);backdrop-filter:blur(8px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem;transition:all .3s}
.pmodal{background:#fff;border-radius:1.5rem;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:2rem;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);border:1px solid rgba(226,232,240,0.8)}
.dark .pmodal{background:linear-gradient(145deg,#1e293b,#0f172a);border-color:rgba(51,65,85,.6)}
.pmodal-title{font-size:1.15rem;font-weight:800;color:#0f172a;margin-bottom:1.5rem;letter-spacing:-0.01em}
.dark .pmodal-title{color:#f8fafc}
.pg2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.ptoggle{display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border-radius:.6rem;margin-bottom:.4rem;background:#f8fafc}
.dark .ptoggle{background:#0f172a}
.ptoggle input[type=checkbox]{width:1.1rem;height:1.1rem;accent-color:#0ea5e9;cursor:pointer}
.empty-state{text-align:center;padding:4rem 2rem;color:#94a3b8;background:rgba(241,245,249,0.4);border-radius:16px;border:2px dashed #e2e8f0;margin:1rem 0}
.dark .empty-state{background:rgba(30,41,59,0.3);border-color:#334155}
.code-chip{font-family:monospace;font-size:.95rem;font-weight:800;color:#4f46e5;background:linear-gradient(135deg,rgba(79,70,229,.1),rgba(99,102,241,.15));padding:.25rem .65rem;border-radius:.5rem;border:1px dashed rgba(99,102,241,.4);letter-spacing:.08em}
.dark .code-chip{color:#818cf8;border-color:rgba(129,140,248,.4)}
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
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
                <div style="flex:1">
                    <div class="pc-title">{{ $p->desc }}</div>
                    <div class="pc-sub">📅 <span style="font-weight:600">{{ $p->start?->format('d M Y') }}</span> — <span style="font-weight:600">{{ $p->end?->format('d M Y') }}</span>
                        @php $levelLabels = ['semester_2' => 'Semester 2', 'semester_3' => 'Semester 3']; @endphp
                        <span style="color:#cbd5e1;margin:0 8px">|</span>🎓 <strong>{{ $levelLabels[$p->level] ?? $p->level }}</strong>
                    </div>
                    <div class="pc-sub" style="margin-top:.4rem;background:var(--card-bg, rgba(241,245,249,0.5));padding:0.5rem 0.75rem;border-radius:0.5rem;display:inline-block">
                        💰 Nominal: <strong style="color:#0ea5e9;font-size:0.95rem">Rp {{ number_format($p->level === 'semester_3' ? $p->semester_3 : $p->semester_2,0,',','.') }}</strong>
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem">
                        @if($p->va)   <span class="pb pb-blue">🏧 VA</span>   @endif
                        @if($p->qris) <span class="pb pb-violet">📱 QRIS</span> @endif
                        @if($p->cs)   <span class="pb pb-yellow">🏪 CS</span>  @endif
                        @php $sc=match($p->status){'active','PAID'=>'pb-green','PENDING'=>'pb-yellow','EXPIRED'=>'pb-red',default=>'pb-gray'}; @endphp
                        <span class="pb {{ $sc }}">{{ strtoupper($p->status) }}</span>
                    </div>
                    @if($p->send_reminder && $p->reminder_days_before)
                    <div class="pc-sub" style="margin-top:.6rem;font-size:0.75rem;color:#f59e0b;font-weight:600">🔔 Reminder H-{{ $p->reminder_days_before }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0">
                    <button type="button" class="pbtn-e" wire:click="openEditPayment({{ $p->id }})" title="Edit">✏️</button>
                    <button type="button" class="pbtn-d" wire:click="deletePayment({{ $p->id }})"
                        wire:confirm="Hapus penagihan '{{ addslashes($p->desc) }}'?" title="Hapus">🗑️</button>
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
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <span class="code-chip">{{ $k->code }}</span>
                        <span class="pc-title" style="margin-bottom:0">{{ $k->name }}</span>
                        <span class="pb {{ $k->is_active ? 'pb-green' : 'pb-red' }}" style="padding:0.15rem 0.5rem;font-size:0.7rem">{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="pc-sub" style="margin-top:.4rem;background:var(--card-bg, rgba(241,245,249,0.5));padding:0.5rem 0.75rem;border-radius:0.5rem;display:inline-block">
                        💰 Potongan: <strong style="color:#10b981;font-size:0.95rem">Rp {{ number_format($k->amount,0,',','.') }}</strong>
                    </div>
                    <div class="pc-sub" style="margin-top:.4rem">📅 <span style="font-weight:600">{{ $k->valid_from?->format('d M Y') }}</span> — <span style="font-weight:600">{{ $k->valid_until?->format('d M Y') }}</span></div>
                    @if($k->used_by)
                    <div style="font-size:.8rem;color:#059669;margin-top:.6rem;font-weight:600;display:flex;align-items:center;gap:.25rem">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Dipakai oleh {{ $k->used_by }} <span style="font-size:0.75rem;opacity:0.8;font-weight:400;margin-left:.2rem">({{ $k->used_at ? $k->used_at->format('d M Y H:i') : '' }})</span>
                    </div>
                    @else
                    <div style="font-size:.8rem;color:#94a3b8;margin-top:.6rem;font-weight:600;display:flex;align-items:center;gap:.25rem">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Belum dipakai
                    </div>
                    @endif
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0;flex-direction:column;align-items:flex-end">
                    <button type="button" class="pbtn-t" wire:click="toggleKuponStatus({{ $k->id }})" title="{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">{{ $k->is_active ? '🔒' : '🔓' }}</button>
                    <div style="display:flex;gap:.5rem">
                        <button type="button" class="pbtn-e" wire:click="openEditKupon({{ $k->id }})" title="Edit">✏️</button>
                        <button type="button" class="pbtn-d" wire:click="deleteKupon({{ $k->id }})" wire:confirm="Hapus kupon '{{ $k->code }}'?" title="Hapus">🗑️</button>
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
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
                <div>
                    <div class="pc-title">{{ $log->civitas?->name ?? $log->civitas_id }}</div>
                    <div class="pc-sub" style="font-weight:600">
                        {{ ucfirst($log->angkatan) }} 
                        <span style="color:#cbd5e1;font-weight:400;margin:0 8px">|</span> 
                        <strong style="color:#0ea5e9;font-size:0.95rem">Rp {{ number_format($log->amount,0,',','.') }}</strong>
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem">
                        @if($log->coupon_id)
                            <span class="pb pb-blue">🎟️ {{ $log->coupon?->code ?? '-' }}</span>
                            <span class="pb pb-green">-Rp {{ number_format($log->discount_amount,0,',','.') }}</span>
                        @else
                            <span class="pb pb-gray" style="opacity:0.8;font-weight:600">🎟️ Tidak Ada Kupon</span>
                        @endif
                    </div>
                    <div class="pc-sub" style="margin-top:.75rem;font-size:0.75rem;display:flex;align-items:center;gap:0.5rem">
                        <span style="background:rgba(100,116,139,0.1);padding:0.25rem 0.5rem;border-radius:6px;color:#475569;font-weight:600">{{ $log->payment?->desc ?? '-' }}</span>
                        <span style="color:#94a3b8;font-weight:500">⏰ {{ $log->created_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
                <span class="pb {{ $log->status === 'PAID' ? 'pb-green' : 'pb-yellow' }}" style="flex-shrink:0;font-size:0.8rem;padding:0.35rem 0.85rem;box-shadow:0 2px 4px rgba(0,0,0,0.05)">{{ $log->status }}</span>
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

        <div style="margin-bottom:.9rem">
            <label class="plbl">Level *</label>
            <select wire:model="level" class="pinput" style="width:100%">
                <option value="">-- Pilih Level --</option>
                <option value="semester_2">Semester 2</option>
                <option value="semester_3">Semester 3</option>
            </select>
            @error('level') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom:.9rem">
            <label class="plbl">Nominal {{ $level === 'semester_3' ? 'Semester 3' : ($level === 'semester_2' ? 'Semester 2' : '(pilih level dulu)') }} (Rp) *</label>
            <input wire:model="nominal" type="number" min="0" class="pinput" placeholder="0">
            @error('nominal') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
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
