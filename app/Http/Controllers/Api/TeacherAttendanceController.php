<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TeacherAttendanceController extends Controller
{
    /**
     * Daftar ustadz untuk dropdown absensi.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function teachers(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = Teacher::query()
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $teachers = $query->get(['id', 'name', 'photo']);

        return response()->json([
            'status' => 'success',
            'data' => $teachers->map(fn ($teacher) => [
                'id'    => $teacher->id,
                'name'  => $teacher->name,
                'photo' => $this->photoUrl($teacher->photo),
            ]),
        ]);
    }

    /**
     * Catat absensi ustadz secara manual.
     *
     * Payload:
     *   - teacher_id      : id ustadz (wajib)
     *   - attendance_date : datetime "Y-m-d H:i" atau "Y-m-d H:i:s" (wajib)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'attendance_date' => 'required|date',
        ]);

        $dateInput = str_replace('T', ' ', $validated['attendance_date']);

        // Normalisasi ke format "Y-m-d H:i:s" agar konsisten
        $normalized = date('Y-m-d H:i:s', strtotime($dateInput));

        // Cegah duplikat: satu ustadz hanya satu record per hari yang sama
        $exists = TeacherAttendance::where('teacher_id', $validated['teacher_id'])
            ->whereDate('attendance_date', date('Y-m-d', strtotime($normalized)))
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'code' => 'DUPLICATE_ATTENDANCE',
                'message' => 'Absensi ustadz sudah tercatat pada tanggal tersebut.',
            ], 409);
        }

        try {
            $attendance = TeacherAttendance::create([
                'teacher_id' => $validated['teacher_id'],
                'attendance_date' => $normalized,
                'created_by' => $request->user()?->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan absensi ustadz: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan absensi. Silakan coba lagi.',
            ], 500);
        }

        $attendance->load('teacher');

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi ustadz berhasil dicatat!',
            'data' => [
                'id' => $attendance->id,
                'teacher_id' => $attendance->teacher_id,
                'teacher_name' => $attendance->teacher?->name,
                'attendance_date' => $attendance->attendance_date,
                'created_by' => $attendance->created_by,
                'created_at' => $attendance->created_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Riwayat absensi ustadz dengan filter opsional.
     *
     * Query params (opsional):
     *   - teacher_id : filter per ustadz
     *   - date_from  : "Y-m-d" batas awal
     *   - date_to    : "Y-m-d" batas akhir
     *   - per_page   : jumlah per halaman (default 20, maks 100)
     *   - page       : halaman
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'sometimes|integer|exists:teachers,id',
            'date_from'  => 'sometimes|date_format:Y-m-d',
            'date_to'    => 'sometimes|date_format:Y-m-d',
            'per_page'   => 'sometimes|integer|min:1|max:100',
            'page'       => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter query tidak valid.',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $dateFrom = $request->date('date_from');
        $dateTo   = $request->date('date_to');

        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'date_from tidak boleh lebih besar dari date_to.',
            ], 422);
        }

        $query = TeacherAttendance::query()
            ->with(['teacher:id,name,photo', 'creator:id,name'])
            ->orderByDesc('attendance_date');

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($dateFrom) {
            $query->whereDate('attendance_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('attendance_date', '<=', $dateTo);
        }

        $perPage = min($request->integer('per_page', 20), 100);

        $paginated = $query->paginate($perPage)->withQueryString();

        $data = $paginated->getCollection()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'teacher' => [
                    'id'    => $attendance->teacher?->id,
                    'name'  => $attendance->teacher?->name,
                    'photo' => $this->photoUrl($attendance->teacher?->photo),
                ],
                'attendance_date' => $attendance->attendance_date,
                'created_by'      => $attendance->created_by,
                'created_by_name' => $attendance->creator?->name,
                'created_at'      => $attendance->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last'  => $paginated->url($paginated->lastPage()),
                'prev'  => $paginated->previousPageUrl(),
                'next'  => $paginated->nextPageUrl(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────

    /**
     * Konversi path foto relatif (public disk) ke URL absolut.
     * Jika sudah berupa URL lengkap atau null, kembalikan apa adanya.
     */
    private function photoUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // Sudah absolute URL — jangan ubah
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $url = Storage::disk('public')->url($path);

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : url($url);
    }

    /**
     * Hapus record absensi ustadz.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $attendance = TeacherAttendance::find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'code' => 'NOT_FOUND',
                'message' => 'Data absensi tidak ditemukan.',
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil dihapus.',
        ]);
    }
}
