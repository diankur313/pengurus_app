@php
    use App\Models\QuizSubmission;
    use App\Models\CivitasPendidikan;

    $quiz     = $getRecord();
    $schedule = $quiz->schedules->first();

    if ($schedule) {
        $total       = CivitasPendidikan::where('level_angkatan', $schedule->level)->count();
        $done        = QuizSubmission::where('quiz_id', $quiz->id)->count();
        $percent     = $total > 0 ? round(($done / $total) * 100) : 0;
    } else {
        $total   = 0;
        $done    = 0;
        $percent = 0;
    }

    $barColor = match(true) {
        $percent >= 80 => '#22c55e',
        $percent >= 50 => '#eab308',
        default        => '#3b82f6',
    };
@endphp

<div style="min-width: 140px; max-width: 200px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <span style="font-size: 11px; color: #6b7280;">{{ $done }} / {{ $total }}</span>
        <span style="font-size: 12px; font-weight: 700; color: {{ $barColor }};">{{ $percent }}%</span>
    </div>
    <div style="height: 8px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
        <div style="height: 100%; width: {{ $percent }}%; background: {{ $barColor }}; border-radius: 9999px; transition: width 0.5s ease;"></div>
    </div>
</div>
