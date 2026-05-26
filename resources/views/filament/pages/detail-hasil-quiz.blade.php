<x-filament-panels::page>
    {{-- Header Info --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a4f 100%); border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; color: #fff;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; margin-bottom: 4px;">Quiz</div>
                <div style="font-size: 20px; font-weight: 700;">{{ $this->quizTitle }}</div>
            </div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div style="text-align: center; background: rgba(255,255,255,0.1); border-radius: 8px; padding: 10px 18px;">
                    <div style="font-size: 11px; opacity: 0.7; margin-bottom: 2px;">Angkatan</div>
                    <div style="font-weight: 600;">{{ $this->angkatan }}</div>
                </div>
                <div style="text-align: center; background: rgba(255,255,255,0.1); border-radius: 8px; padding: 10px 18px;">
                    <div style="font-size: 11px; opacity: 0.7; margin-bottom: 2px;">Mengerjakan</div>
                    <div style="font-weight: 600;">{{ $this->totalSubmissions }} / {{ $this->totalSiswa }}</div>
                </div>
                @if($this->hasEssay && $this->belumReview > 0)
                <div style="text-align: center; background: rgba(234,179,8,0.25); border: 1px solid rgba(234,179,8,0.5); border-radius: 8px; padding: 10px 18px;">
                    <div style="font-size: 11px; opacity: 0.85; margin-bottom: 2px;">⚠️ Essay Belum Dinilai</div>
                    <div style="font-weight: 700; color: #fde047;">{{ $this->belumReview }} siswa</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
