<?php

return [
    'calendar_id'          => env('GOOGLE_CALENDAR_ID', 'primary'),
    'service_account_path' => env('GOOGLE_SERVICE_ACCOUNT_PATH', 'storage/app/google/service-account.json'),

    // OAuth2 credentials (untuk Meet API — auto meet link)
    'oauth_client_id'     => env('GOOGLE_OAUTH_CLIENT_ID', ''),
    'oauth_client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET', ''),
    'oauth_redirect_uri'  => env('GOOGLE_OAUTH_REDIRECT_URI', '/google/auth/callback'),
    'oauth_token_path'    => storage_path('app/google/oauth-token.json'),
];
