<?php

namespace App\Http\Controllers;

use App\Exports\PpabPaymentInternalExport;
use App\Exports\PpabPaymentPanitiaExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PpabFinanceReportController extends Controller
{
    public function exportInternal(Request $request)
    {
        $sessionId = $this->resolveSessionId($request);
        $sessionLabel = $this->getSessionLabel($sessionId);

        $filename = 'PPAB_Report_Internal_' . $sessionLabel . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new PpabPaymentInternalExport($sessionId),
            $filename
        );
    }

    public function exportPanitia(Request $request)
    {
        $sessionId = $this->resolveSessionId($request);
        $sessionLabel = $this->getSessionLabel($sessionId);

        $filename = 'PPAB_Report_Panitia_' . $sessionLabel . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new PpabPaymentPanitiaExport($sessionId),
            $filename
        );
    }

    private function resolveSessionId(Request $request): string
    {
        if ($request->filled('session')) {
            $sessionId = (string) $request->query('session');

            $exists = DB::connection('ppab')
                ->table('ppab_sessions')
                ->where('uuid', $sessionId)
                ->exists();

            if ($exists) {
                return $sessionId;
            }
        }

        return (string) DB::connection('ppab')
            ->table('ppab_sessions')
            ->latest('id')
            ->value('uuid');
    }

    private function getSessionLabel(string $sessionId): string
    {
        $session = DB::connection('ppab')
            ->table('ppab_sessions')
            ->where('uuid', $sessionId)
            ->first(['id', 'session_date_start']);

        if (! $session) {
            return 'Unknown_Session';
        }

        $date = substr((string) $session->session_date_start, 0, 10);

        return 'Sesi_' . $session->id . '_' . str_replace('-', '', $date);
    }
}
