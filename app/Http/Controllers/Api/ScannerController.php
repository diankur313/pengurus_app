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

        if (count($parts) !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format QR tidak valid. Harap scan QR dari e-sii.',
            ], 400);
        }

        $civitasUuid = trim($parts[0]);
        $scheduleUuid = trim($parts[1]);

        try {
            // 1. Validasi Civitas (Siswa)
            $civitas = CivitasPendidikan::where('uuid', $civitasUuid)->first();
            if (!$civitas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Siswa (Civitas) tidak ditemukan.',
                ], 404);
            }

            // 2. Validasi Jadwal (Schedule)
            $schedule = EducationSchedule::where('uuid', $scheduleUuid)->first();
            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal Pembelajaran tidak ditemukan.',
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

            // 4. Insert Absensi
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
