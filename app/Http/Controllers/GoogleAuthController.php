<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke Google OAuth2 consent screen.
     */
    public function redirect()
    {
        $client = $this->buildClient();

        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    /**
     * Handle callback dari Google OAuth2.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization ditolak: ' . $request->get('error'),
            ], 400);
        }

        $code = $request->get('code');
        if (!$code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization code tidak ditemukan.',
            ], 400);
        }

        $client = $this->buildClient();

        // Exchange code → access_token + refresh_token
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token exchange gagal: ' . ($token['error_description'] ?? $token['error']),
            ], 400);
        }

        // Simpan token ke file
        $tokenPath = config('google.oauth_token_path');
        $dir = dirname($tokenPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));

        Log::info('GoogleAuth: OAuth token berhasil disimpan', [
            'token_path' => $tokenPath,
            'scopes'     => $token['scope'] ?? '',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => '✅ Google OAuth berhasil! Meet link sekarang akan otomatis dibuat saat jadwal online disimpan.',
        ]);
    }

    /**
     * Cek status OAuth token.
     */
    public function status()
    {
        $tokenPath = config('google.oauth_token_path');

        if (!file_exists($tokenPath)) {
            return response()->json([
                'status'    => 'not_configured',
                'message'   => 'OAuth token belum dikonfigurasi. Kunjungi /google/auth untuk setup.',
                'auth_url'  => url('/google/auth'),
            ]);
        }

        $token = json_decode(file_get_contents($tokenPath), true);

        return response()->json([
            'status'       => 'configured',
            'has_refresh'  => !empty($token['refresh_token']),
            'created'      => isset($token['created']) ? date('Y-m-d H:i:s', $token['created']) : null,
        ]);
    }

    /**
     * Build Google OAuth2 client.
     */
    protected function buildClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('google.oauth_client_id'));
        $client->setClientSecret(config('google.oauth_client_secret'));
        $client->setRedirectUri(url(config('google.oauth_redirect_uri')));
        $client->setAccessType('offline');       // Dapat refresh_token
        $client->setPrompt('consent');           // Force consent → pastikan dapat refresh_token
        $client->setIncludeGrantedScopes(true);

        // Scopes
        $client->addScope(Calendar::CALENDAR);
        $client->addScope('https://www.googleapis.com/auth/meetings.space.created');

        return $client;
    }
}
