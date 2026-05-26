<x-filament-panels::page>
    {{-- Header Info --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #374151 100%); border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; color: #fff;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <div style="flex: 1; min-width: 200px;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; margin-bottom: 2px;">Quiz</div>
                <div style="font-size: 18px; font-weight: 700;">{{ $this->quizTitle }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="text-align: right;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; margin-bottom: 2px;">Siswa</div>
                    <div style="font-size: 16px; font-weight: 700;">{{ $this->siswaNama }}</div>
                </div>
                <img src="{{ $this->siswaPhoto }}" alt="{{ $this->siswaNama }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); box-shadow: 0 4px 6px rgba(0,0,0,0.1);" />
            </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            @if($this->totalScore !== '-')
            <div style="background: rgba(255,255,255,0.12); border-radius: 8px; padding: 8px 16px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.7;">Total Score</div>
                <div style="font-size: 20px; font-weight: 800; color: #86efac;">{{ $this->totalScore }}</div>
            </div>
            @endif
            @if($this->mcScore !== '-')
            <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 16px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.7;">PG Score</div>
                <div style="font-size: 16px; font-weight: 700;">{{ $this->mcScore }}</div>
            </div>
            @endif
            @if($this->hasEssay)
            <div style="background: {{ $this->essayNull ? 'rgba(234,179,8,0.2)' : 'rgba(255,255,255,0.1)' }}; {{ $this->essayNull ? 'border: 1px solid rgba(234,179,8,0.5);' : '' }} border-radius: 8px; padding: 8px 16px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.7;">Essay Score</div>
                @if($this->essayNull)
                <div style="font-size: 13px; font-weight: 600; color: #fde047;">⚠ Belum Dinilai</div>
                @else
                <div style="font-size: 16px; font-weight: 700;">{{ $this->essayScore }}</div>
                @endif
            </div>
            @endif
            <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 16px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.7;">Status</div>
                <div style="font-size: 13px; font-weight: 600;">
                    {{ match($this->status) { 'submitted' => 'Selesai', 'reviewed' => 'Sudah Ditinjau', default => ucfirst($this->status) } }}
                </div>
            </div>
        </div>
    </div>

    {{-- Answer Table --}}
    {{ $this->table }}
</x-filament-panels::page>