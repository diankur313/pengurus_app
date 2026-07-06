@php
    $state  = $getState();
    $record = $state['record'] ?? null;
    $rows   = $state['rows'] ?? [];

    $resolve = function ($field, $record) {
        $key    = $field['key'] ?? '';
        $format = $field['format'] ?? null;
        $raw    = $record->{$key} ?? null;

        if ($format === 'ucfirst') {
            return $raw ? ucfirst($raw) : '-';
        }
        if ($format === 'date') {
            return $raw ? \Carbon\Carbon::parse($raw)->translatedFormat('d F Y') : '-';
        }
        if ($format === 'datetime') {
            return $raw ? \Carbon\Carbon::parse($raw)->translatedFormat('d F Y H:i:s') : '-';
        }
        if ($format === 'bool_ya_tidak') {
            return $raw ? 'Ya' : 'Tidak';
        }
        if ($format === 'sudah_bekerja') {
            return $raw ? 'Sudah Bekerja' : 'Belum Bekerja';
        }
        if ($format === 'nama_angkatan') {
            if (!$raw) return '-';
            try {
                $detail = \Illuminate\Support\Facades\DB::connection('ppab')
                    ->table('ppab_nama_angkatans')
                    ->where('nama_angkatan', $raw)
                    ->first();
                if ($detail && $detail->tahun) {
                    return "{$raw} ({$detail->tahun})";
                }
            } catch (\Exception $e) {}
            return $raw;
        }
        if ($format === 'stage_badge') {
            return null;
        }
        if (str_starts_with((string) $format, 'region:')) {
            $table = substr($format, 7);
            if (!$raw) return '-';
            try {
                $regionRecord = \Illuminate\Support\Facades\DB::connection('ppab')
                    ->table($table)
                    ->where('id', $raw)
                    ->first();
                return $regionRecord ? ($regionRecord->{$table} ?? $raw) : $raw;
            } catch (\Exception $e) {
                return $raw;
            }
        }

        return $raw ?? '-';
    };

    $stageBadgeClass = function ($state) {
        return match ($state) {
            'paid_payment'    => 'fi-badge-color-success',
            'pending_payment' => 'fi-badge-color-warning',
            'expired_payment' => 'fi-badge-color-danger',
            default           => 'fi-badge-color-gray',
        };
    };
@endphp

@if ($record)
{{-- Gunakan class Filament native agar warna ikut tema (sama persis dg contained repeatable entry) --}}
<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 overflow-hidden">
    @foreach ($rows as $row)
        @php
            $cols       = count($row);
            $isFullSpan = $cols === 1 && ($row[0]['span'] ?? '') === 'full';
        @endphp

        <div class="grid divide-x divide-gray-950/5 dark:divide-white/5 border-b border-gray-950/5 dark:border-white/5 last:border-b-0"
             style="grid-template-columns: repeat({{ $isFullSpan ? 1 : $cols }}, minmax(0, 1fr))">

            @foreach ($row as $field)
                @php
                    $format = $field['format'] ?? null;
                    $raw    = $record->{$field['key'] ?? ''} ?? null;
                @endphp

                <div class="px-4 py-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">
                        {{ $field['label'] }}
                    </p>

                    @if ($format === 'stage_badge')
                        @php
                            $label = ucfirst(str_replace('_', ' ', $raw ?? ''));
                            $cls   = $stageBadgeClass($raw ?? '');
                        @endphp
                        <span @class([
                            'fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 py-1 w-fit',
                            'fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30' => true,
                            '[--c-400:theme(colors.green.400)] [--c-50:theme(colors.green.50)] [--c-600:theme(colors.green.600)]' => ($raw === 'paid_payment'),
                            '[--c-400:theme(colors.yellow.400)] [--c-50:theme(colors.yellow.50)] [--c-600:theme(colors.yellow.600)]' => ($raw === 'pending_payment'),
                            '[--c-400:theme(colors.red.400)] [--c-50:theme(colors.red.50)] [--c-600:theme(colors.red.600)]' => ($raw === 'expired_payment'),
                            '[--c-400:theme(colors.gray.400)] [--c-50:theme(colors.gray.50)] [--c-600:theme(colors.gray.600)]' => !in_array($raw, ['paid_payment','pending_payment','expired_payment']),
                        ])>
                            {{ $label ?: '-' }}
                        </span>
                    @else
                        @php $val = $resolve($field, $record); @endphp
                        <p class="text-sm text-gray-950 dark:text-white">
                            {{ filled($val) ? $val : '-' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endif
