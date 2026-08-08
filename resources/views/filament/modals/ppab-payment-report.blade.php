@php
    $sessions = \Illuminate\Support\Facades\DB::connection('ppab')
        ->table('ppab_sessions')
        ->orderByDesc('id')
        ->get(['uuid', 'session_date_start', 'session_date_end', 'id']);
    
    $latestSession = $sessions->first()?->uuid ?? '';
@endphp

<div class="space-y-4" x-data="{ selectedSession: '{{ $latestSession }}' }">
    <!-- Session Selector -->
    <div class="mb-6">
        <label class="block text-sm font-medium mb-2 dark:text-gray-200">Pilih Sesi PPAB</label>
        <select 
            x-model="selectedSession"
            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
        >
            @foreach($sessions as $session)
                @php
                    $startDate = \Carbon\Carbon::parse($session->session_date_start);
                    $endDate = \Carbon\Carbon::parse($session->session_date_end);
                    $paymentCount = \Illuminate\Support\Facades\DB::connection('ppab')
                        ->table('ppab_transactions_xendit')
                        ->where('id_session', $session->uuid)
                        ->where('status', 'PAID')
                        ->count();
                @endphp
                <option value="{{ $session->uuid }}">
                    Sesi #{{ $session->id }} - {{ $startDate->format('d M Y') }} s/d {{ $endDate->format('d M Y') }}
                    @if($paymentCount > 0)
                        ({{ $paymentCount }} pembayaran)
                    @else
                        (belum ada pembayaran)
                    @endif
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Pilih sesi PPAB yang ingin Anda generate report-nya
        </p>
    </div>

    <!-- Report Type Buttons -->
    <div class="grid grid-cols-2 gap-4">
        <x-filament::button
            type="button"
            color="success"
            icon="heroicon-o-document-chart-bar"
            size="lg"
            class="h-24"
            tag="a"
            x-bind:href="`{{ route('ppab.finance.internal') }}?session=${selectedSession}`"
            target="_blank"
        >
            <div class="flex flex-col items-center">
                <span class="text-lg font-semibold">Internal</span>
                <span class="text-xs opacity-75">Report untuk internal</span>
            </div>
        </x-filament::button>

        <x-filament::button
            type="button"
            color="primary"
            icon="heroicon-o-user-group"
            size="lg"
            class="h-24"
            tag="a"
            x-bind:href="`{{ route('ppab.finance.panitia') }}?session=${selectedSession}`"
            target="_blank"
        >
            <div class="flex flex-col items-center">
                <span class="text-lg font-semibold">Panitia</span>
                <span class="text-xs opacity-75">Report untuk panitia</span>
            </div>
        </x-filament::button>
    </div>

    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-xs text-blue-800 dark:text-blue-200">
            <strong>Internal:</strong> Kolom sederhana (Nama, Channel, Bank, Amount, Sisa Pelunasan)<br>
            <strong>Panitia:</strong> Kolom lengkap dengan breakdown fee (VAT, Fee Sysdev, Withdrawable)
        </p>
    </div>
</div>
