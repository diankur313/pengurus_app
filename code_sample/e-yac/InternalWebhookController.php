<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\archery_transaction;
use App\User;
use Illuminate\Support\Facades\Log;

class InternalWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        // 1. Verifikasi Keamanan
        $token = $request->header('X-Internal-Token');
        if ($token !== env('INTERNAL_WEBHOOK_SECRET_ARCHERY')) {
            Log::warning('Internal Webhook: Unauthorized access attempt.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Validasi Payload
        $externalId = $request->input('external_id');
        $status = $request->input('status');

        if (!$externalId || !$status) {
            Log::warning('Internal Webhook: Invalid payload.', $request->all());
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 3. Cari Data Transaksi E-YAC
        $transaction = archery_transaction::where('external_Id', $externalId)->first();

        if (!$transaction) {
            Log::warning('Internal Webhook: Transaction not found.', ['external_id' => $externalId]);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // 4. Update Status Database
        $transaction->update(['status' => $status]);

        if ($transaction->id_user) {
            User::where('id', $transaction->id_user)->update(['payment_status' => $status]);
        }

        // 5. Response
        return response()->json(['message' => 'Success'], 200);
    }
}
