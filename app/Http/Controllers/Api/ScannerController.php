<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CivitasPendidikan;
use App\Models\EducationSchedule;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;

class ScannerController extends Controller
{
    /**
     * Handle scan from mobile app.
     * Expected payload: { "qr_data": "UUID_civitas|UUID_schedule" }
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        $qrData = $request->qr_data;
        $parts = explode('|', $qrData);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'QR tidak valid atau tidak dikenali. Harap scan QR resmi dari e-sii.',
            ], 400);
        }

        $civitasUuid = trim($parts[0]);
        $scheduleUuid = trim($parts[1]);

        try {
            // 1. Validasi Civitas (Siswa) — QR palsu/asal akan gagal di sini
            $civitas = CivitasPendidikan::where('uuid', $civitasUuid)->first();
            if (!$civitas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'QR tidak valid atau tidak dikenali. Data Siswa (Civitas) tidak ditemukan.',
                ], 404);
            }

            // 2. Validasi Jadwal (Schedule) — QR palsu/asal akan gagal di sini
            $schedule = EducationSchedule::where('uuid', $scheduleUuid)->first();
            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'QR tidak valid atau tidak dikenali. Jadwal Pembelajaran tidak ditemukan.',
                ], 404);
            }

            // 3. Validasi Duplicate Scan
            $alreadyScanned = DB::table('attendances')
                ->where('civitas_id', $civitasUuid)
                ->where('schedule_id', $scheduleUuid)
                ->exists();

            if ($alreadyScanned) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Siswa sudah melakukan absensi pada jadwal ini.',
                    'data' => [
                        'student_name' => $civitas->name ?? 'Siswa',
                        'schedule_title' => $schedule->title,
                    ]
                ], 409); // 409 Conflict
            }

            // 4. Validasi Waktu Jadwal (belum mulai / sudah lewat)
            $now = now();

            if ($now->lt($schedule->start_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal belum dimulai. Absensi baru bisa dilakukan mulai ' . $schedule->start_at->locale('id')->translatedFormat('d M Y, H:i') . ' WIB.',
                    'data' => [
                        'student_name' => $civitas->name ?? 'Siswa',
                        'schedule_title' => $schedule->title,
                        'start_at' => $schedule->start_at->format('Y-m-d H:i:s'),
                    ],
                ], 422);
            }

            if ($now->gt($schedule->end_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal sudah berakhir pada ' . $schedule->end_at->locale('id')->translatedFormat('d M Y, H:i') . ' WIB. Absensi tidak dapat dilakukan lagi.',
                    'data' => [
                        'student_name' => $civitas->name ?? 'Siswa',
                        'schedule_title' => $schedule->title,
                        'end_at' => $schedule->end_at->format('Y-m-d H:i:s'),
                    ],
                ], 422);
            }

            // 5. Insert Absensi
            DB::table('attendances')->insert([
                'civitas_id' => $civitasUuid,
                'schedule_id' => $scheduleUuid,
                'scanned_by_user_id' => Auth::id(),
                'status' => 'hadir',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil dicatat!',
                'data' => [
                    'student_name' => $civitas->name ?? 'Siswa',
                    'schedule_title' => $schedule->title,
                    'time' => now()->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner API Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat mencatat absensi.',
            ], 500);
        }
    }
}
