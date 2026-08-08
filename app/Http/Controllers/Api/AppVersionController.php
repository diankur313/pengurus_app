<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function check(Request $request)
    {
        $platform = $request->query('platform', 'android');

        $mandatory = AppVersion::where('platform', $platform)
            ->where('is_mandatory', true)
            ->orderByDesc('version_code')
            ->first();

        return response()->json([
            'platform' => $platform,
            'mandatory_version_code' => $mandatory?->version_code,
            'mandatory_version_name' => $mandatory?->version_name,
            'custom_message' => $mandatory?->custom_message,
            'download_url' => $mandatory?->download_url,
        ]);
    }
}
