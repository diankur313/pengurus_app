@php
    $record     = $getRecord();
    $link       = $record->meeting_link ?? null;
    $isOnline   = $record->attendance_mode === 'online';
    $isExpired  = $record->end_at && $record->end_at->isPast();
    $wrapperId  = 'meet-col-' . $record->id;
@endphp

{{-- Inject global style once --}}
@once
<style>
    /* Force all meet-col wrappers and their Filament cell parents to stay horizontal */
    [id^="meet-col-"] {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        gap: 6px !important;
    }
    /* Filament v3 table cell content wrapper */
    .fi-ta-col-wrap:has([id^="meet-col-"]),
    .fi-ta-cell:has([id^="meet-col-"]) > * {
        overflow: visible !important;
        display: block !important;
    }
</style>
@endonce

@if ($isOnline && $link)
    <span id="{{ $wrapperId }}">

        {{-- Join Meet badge --}}
        @if ($isExpired)
            {{-- Expired: non-clickable --}}
            <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;font-size:0.72rem;font-weight:700;border-radius:8px;border:1.5px solid #d1d5db;background-color:#f3f4f6;color:#9ca3af;white-space:nowrap;cursor:not-allowed;" title="Jadwal sudah berakhir">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="width:13px;height:13px;flex-shrink:0;">
                    <path d="M15.75 10.5l4.72-2.285a.75.75 0 011.03.682v8.196a.75.75 0 01-1.03.682l-4.72-2.285M9 9h1.5m3 12H6.225c-1.124 0-1.686 0-2.115-.218a2 2 0 01-.876-.876C3 15.486 3 14.924 3 13.8V8.2c0-1.124 0-1.686.218-2.115a2 2 0 01.876-.876C4.539 5 5.101 5 6.225 5h7.55c1.124 0 1.686 0 2.115.218a2 2 0 01.876.876c.218.429.218.991.218 2.115v2.85M9 13h6"/>
                </svg>
                Berakhir
            </span>
        @else
            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
               onclick="event.stopPropagation()"
               style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;font-size:0.72rem;font-weight:700;border-radius:8px;border:1.5px solid #10b981;background-color:rgba(16,185,129,0.12);color:#059669;text-decoration:none;white-space:nowrap;"
               onmouseover="this.style.backgroundColor='rgba(16,185,129,0.25)'"
               onmouseout="this.style.backgroundColor='rgba(16,185,129,0.12)'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     style="width:13px;height:13px;flex-shrink:0;">
                    <path d="M15.75 10.5l4.72-2.285a.75.75 0 011.03.682v8.196a.75.75 0 01-1.03.682l-4.72-2.285M9 9h1.5m3 12H6.225c-1.124 0-1.686 0-2.115-.218a2 2 0 01-.876-.876C3 15.486 3 14.924 3 13.8V8.2c0-1.124 0-1.686.218-2.115a2 2 0 01.876-.876C4.539 5 5.101 5 6.225 5h7.55c1.124 0 1.686 0 2.115.218a2 2 0 01.876.876c.218.429.218.991.218 2.115v2.85M9 13h6"/>
                </svg>
                Join Meet
            </a>
        @endif<!--

        --><span role="button"
            tabindex="0"
            data-meet-link="{{ $link }}"
            onclick="event.stopPropagation();esCopyMeetLink(this)"
            onkeydown="if(event.key==='Enter'||event.key===' '){event.stopPropagation();esCopyMeetLink(this);}"
            style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;padding:0;border:1.5px solid #d1d5db;background:#fff;border-radius:7px;cursor:pointer;flex-shrink:0;vertical-align:middle;"
            onmouseover="if(!this.dataset.copied){this.style.backgroundColor='#f9fafb';this.style.borderColor='#9ca3af';}"
            onmouseout="if(!this.dataset.copied){this.style.backgroundColor='#fff';this.style.borderColor='#d1d5db';}"
            title="Copy link Google Meet">
            <svg id="icon-copy-{{ $record->id }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="width:14px;height:14px;pointer-events:none;">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
        </span>
    </span>

    {{-- JS: aggressively fix ancestor overflow and min-width --}}
    <script>
    (function() {
        var wid = '{{ $wrapperId }}';
        function fix() {
            var el = document.getElementById(wid);
            if (!el) return;
            var node = el.parentElement;
            for (var i = 0; i < 8; i++) {
                if (!node || node === document.body) break;
                var tag = (node.tagName || '').toLowerCase();
                // Remove any class-based flex-col
                node.classList.remove('flex-col');
                // Force inline styles
                node.style.setProperty('overflow', 'visible', 'important');
                if (tag === 'td') {
                    node.style.setProperty('min-width', '190px', 'important');
                    node.style.setProperty('width', '190px', 'important');
                    break;
                }
                node = node.parentElement;
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fix);
        } else {
            fix();
        }
        document.addEventListener('livewire:navigated', fix);
        document.addEventListener('livewire:update', fix);
    })();
    </script>

@else
    <span style="color:#94a3b8;font-size:0.85rem;">—</span>
@endif

<script>
if (typeof window.esCopyMeetLink === 'undefined') {
    window.esCopyMeetLink = function(btn) {
        var link = btn.getAttribute('data-meet-link');
        if (!link) return;
        navigator.clipboard.writeText(link).then(function() {
            btn.dataset.copied = '1';
            btn.style.borderColor = '#10b981';
            btn.style.backgroundColor = '#ecfdf5';
            btn.querySelector('svg').innerHTML = '<polyline points="20 6 9 17 4 12" stroke="#10b981" stroke-width="2.5"></polyline>';
            btn.querySelector('svg').setAttribute('stroke', '#10b981');
            if (typeof Livewire !== 'undefined') { Livewire.dispatch('notify-copied'); }
            setTimeout(function() {
                delete btn.dataset.copied;
                btn.style.borderColor = '#d1d5db';
                btn.style.backgroundColor = '#fff';
                btn.querySelector('svg').innerHTML = '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>';
                btn.querySelector('svg').setAttribute('stroke', '#374151');
            }, 2000);
        }).catch(function() {
            prompt('Copy link ini:', link);
        });
    };
}
</script>
