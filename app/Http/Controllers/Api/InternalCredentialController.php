<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayFee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InternalCredentialController extends Controller
{
    /**
     * Endpoint untuk child app fetch Xendit secret key sebelum membuat invoice.
     * Diamankan dengan Static Bearer Token.
     *
     * GET /api/internal/xendit-credentials?app_id={app_id}
     */
    public function getCredentials(Request $request): JsonResponse
    {
        // Validasi Bearer Token
        if ($request->bearerToken() !== config('services.internal.secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $appId = $request->query('app_id');
        if (empty($appId)) {
            return response()->json(['message' => 'Parameter app_id wajib diisi.'], 422);
        }

        // Verifikasi app_id terdaftar
        $feeRecord = PaymentGatewayFee::where('app_id', $appId)->first();
        if (!$feeRecord) {
            return response()->json(['message' => "Aplikasi dengan app_id '{$appId}' tidak ditemukan."], 404);
        }

        // Tentukan mode berdasarkan konfigurasi per-app dari DB
        $isProduction = $feeRecord->isProduction();
        $secretKey = $isProduction
            ? config('services.xendit.secret_key_live')
            : config('services.xendit.secret_key_test');

        return response()->json([
            'status'     => 'success',
            'app_id'     => $appId,
            'app_name'   => $feeRecord->app_name,
            'mode'       => $isProduction ? 'production' : 'development',
            'secret_key' => $secretKey,
        ]);
    }
}
