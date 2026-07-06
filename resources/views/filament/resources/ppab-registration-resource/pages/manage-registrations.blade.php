<x-filament-panels::page>
<style>
/* ── Tab Bar (absensi style) ── */
.ptab-bar{display:grid;grid-template-columns:1fr 1fr;background:#fff;border-radius:16px;border:1px solid rgba(226,232,240,0.8);overflow:hidden;margin-bottom:1rem;box-shadow:0 4px 15px rgba(0,0,0,.03)}
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
.pb-red{background:rgba(239,68,68,.1);color:#b91c1c;border-color:rgba(239,68,68,.2)}
.pb-gray{background:rgba(100,116,139,.1);color:#475569;border-color:rgba(100,116,139,.2)}
.dark .pb-green{color:#4ade80;border-color:rgba(74,222,128,.2)}
.dark .pb-red{color:#f87171;border-color:rgba(248,113,113,.2)}
.dark .pb-gray{color:#cbd5e1;border-color:rgba(203,213,225,.2)}
/* ── Buttons ── */
.pbtn-p{background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;border:none;border-radius:.6rem;padding:.5rem 1.25rem;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(14,165,233,.25)}
.pbtn-p:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(14,165,233,.35)}
.pbtn-p:active{transform:translateY(0);box-shadow:0 2px 8px rgba(14,165,233,.2)}
.pbtn-d{width:2.2rem;height:2.2rem;display:flex;align-items:center;justify-content:center;border-radius:.6rem;transition:all .2s;font-size:1rem;border:none;cursor:pointer;background:rgba(239,68,68,.1);color:#dc2626}
.pbtn-d:hover{background:#ef4444;color:#fff;transform:scale(1.1)}
/* ── Search / Form ── */
.pinput{width:100%;padding:.65rem 1rem;border:1px solid #e2e8f0;border-radius:.85rem;font-size:.9rem;background:#f8fafc;color:#1e293b;transition:all .2s;box-shadow:inset 0 2px 4px rgba(0,0,0,0.01)}
.pinput:focus{outline:none;border-color:#0ea5e9;background:#fff;box-shadow:0 0 0 4px rgba(14,165,233,0.15)}
.dark .pinput{background:#0f172a;border-color:#334155;color:#e2e8f0;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2)}
.dark .pinput:focus{background:#1e293b;border-color:#38bdf8;box-shadow:0 0 0 4px rgba(56,189,248,0.15)}
.plbl{font-size:.8rem;font-weight:700;color:#334155;margin-bottom:.35rem;display:block;letter-spacing:0.01em}
.dark .plbl{color:#cbd5e1}
/* ── Modal ── */
.pmodal-bg{position:fixed;inset:0;background:rgba(15,23,42,.65);backdrop-filter:blur(8px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem;transition:all .3s}
.pmodal{background:#fff;border-radius:1.5rem;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:2rem;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);border:1px solid rgba(226,232,240,0.8)}
.dark .pmodal{background:linear-gradient(145deg,#1e293b,#0f172a);border-color:rgba(51,65,85,.6)}
.pmodal-title{font-size:1.15rem;font-weight:800;color:#0f172a;margin-bottom:1.5rem;letter-spacing:-0.01em}
.dark .pmodal-title{color:#f8fafc}
.empty-state{text-align:center;padding:4rem 2rem;color:#94a3b8;background:rgba(241,245,249,0.4);border-radius:16px;border:2px dashed #e2e8f0;margin:1rem 0}
.dark .empty-state{background:rgba(30,41,59,0.3);border-color:#334155}
.code-chip{font-family:monospace;font-size:.95rem;font-weight:800;color:#4f46e5;background:linear-gradient(135deg,rgba(79,70,229,.1),rgba(99,102,241,.15));padding:.25rem .65rem;border-radius:.5rem;border:1px dashed rgba(99,102,241,.4);letter-spacing:.08em}
.dark .code-chip{color:#818cf8;border-color:rgba(129,140,248,.4)}
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
        📝 Registrasi
    </button>
    <button type="button" class="ptab-btn" :class="tab===1?'active':''" @click="go(1)">
        🎟️ Voucher
    </button>
</div>

{{-- ── Dots ── --}}
<div class="ptab-dots">
    <div class="ptab-dot" :class="tab===0?'active':''"></div>
    <div class="ptab-dot" :class="tab===1?'active':''"></div>
</div>

{{-- ── Panels ── --}}
<div id="ptab-wrap" class="ptab-wrap"
     @scroll.passive="const w=$event.target;const i=Math.round(w.scrollLeft/w.offsetWidth);if(tab!==i){tab=i;$wire.set('payTab',i)}">

    {{-- ══ PANEL 0 — REGISTRASI ══ --}}
    <div class="ptab-panel">
        {{ $this->table }}
    </div>

    {{-- ══ PANEL 1 — VOUCHER ══ --}}
    <div class="ptab-panel">
        <div style="display:flex;gap:.65rem;align-items:center;margin-bottom:1rem">
            <input wire:model.live.debounce.300ms="searchVoucher" class="pinput" placeholder="🔍 Cari voucher..." style="flex:1">
            <button type="button" class="pbtn-p" wire:click="openCreateVoucher">+ Buat</button>
        </div>

        @forelse($this->vouchers as $v)
        <div class="pc">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <span class="code-chip">{{ $v->code }}</span>
                        @if($v->used_by_user_id)
                            <span class="pb pb-red" style="padding:0.15rem 0.5rem;font-size:0.7rem">Terpakai</span>
                        @else
                            <span class="pb pb-green" style="padding:0.15rem 0.5rem;font-size:0.7rem">Belum Terpakai</span>
                        @endif
                    </div>
                    <div class="pc-sub" style="margin-top:.4rem;background:var(--card-bg, rgba(241,245,249,0.5));padding:0.5rem 0.75rem;border-radius:0.5rem;display:inline-block">
                        💰 Potongan: <strong style="color:#10b981;font-size:0.95rem">Rp {{ number_format($v->discount,0,',','.') }}</strong>
                    </div>
                    @if($v->used_by_user_id)
                    <div style="font-size:.8rem;color:#059669;margin-top:.6rem;font-weight:600;display:flex;align-items:center;gap:.25rem">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Dipakai oleh {{ $v->used_by_name ?? 'User ID: ' . $v->used_by_user_id }} <span style="font-size:0.75rem;opacity:0.8;font-weight:400;margin-left:.2rem">({{ $v->used_at ? $v->used_at->format('d M Y H:i') : '' }})</span>
                    </div>
                    @else
                    <div style="font-size:.8rem;color:#94a3b8;margin-top:.6rem;font-weight:600;display:flex;align-items:center;gap:.25rem">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Belum dipakai
                    </div>
                    @endif
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0;flex-direction:column;align-items:flex-end">
                    <div style="display:flex;gap:.5rem">
                        <button type="button" class="pbtn-d" wire:click="deleteVoucher({{ $v->id }})" wire:confirm="Hapus voucher '{{ $v->code }}'?" title="Hapus">🗑️</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div style="font-size:2.5rem;margin-bottom:.5rem">🎟️</div>
            <div style="font-weight:600">Belum ada voucher</div>
        </div>
        @endforelse
    </div>

</div>{{-- end ptab-wrap --}}
</div>{{-- end x-data --}}

{{-- ══════════════ MODAL VOUCHER ══════════════ --}}
@if($showVoucherModal)
<div class="pmodal-bg" wire:click.self="$set('showVoucherModal',false)">
    <div class="pmodal">
        <div class="pmodal-title">{{ $editVoucherId ? '✏️ Edit Voucher' : '+ Buat Voucher' }}</div>

        @if(!$editVoucherId)
        <div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.75rem;padding:.7rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#6366f1;font-weight:600">
            ✨ Kode 6 karakter auto-generated saat disimpan
        </div>
        @endif

        <div style="margin-bottom:.9rem">
            <label class="plbl">Potongan Harga (Rp) *</label>
            <input wire:model="voucherDiscount" type="number" min="0" class="pinput">
            @error('voucherDiscount') <div style="font-size:.73rem;color:#dc2626;margin-top:.2rem">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;gap:.65rem;justify-content:flex-end;margin-top:1.5rem">
            <button type="button" wire:click="$set('showVoucherModal',false)"
                style="padding:.5rem 1rem;border-radius:.65rem;border:1px solid #e2e8f0;background:transparent;cursor:pointer;font-size:.85rem;color:#64748b">Batal</button>
            <button type="button" class="pbtn-p" wire:click="saveVoucher" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveVoucher">{{ $editVoucherId ? 'Simpan' : 'Buat' }}</span>
                <span wire:loading wire:target="saveVoucher">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
