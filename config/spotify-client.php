<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Spotify API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Spotify application credentials. You can find these in your
    | Spotify Developer Dashboard at https://developer.spotify.com/dashboard
    |
    */

    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Market
    |--------------------------------------------------------------------------
    |
    | The default market (country) for Spotify API requests. This affects
    | which tracks/albums are available. Use ISO 3166-1 alpha-2 country codes.
    | Examples: US, GB, CA, AU, DE, FR, etc.
    |
    */

    'default_market' => env('SPOTIFY_DEFAULT_MARKET', 'US'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Configure how your application authenticates with Spotify's API.
    |
    */

    'auth' => [
        /*
        | Default authentication flow to use:
        | - 'client_credentials': For app-only access (no user context)
        | - 'authorization_code': For user authorization (requires user consent)
        */
        'default_flow' => env('SPOTIFY_AUTH_FLOW', 'client_credentials'),

        /*
        | Scopes for user authorization (used with authorization_code flow)
        | See: https://developer.spotify.com/documentation/web-api/concepts/scopes
        */
        'scopes' => [
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-read-collaborative',
        ],

        /*
        | Redirect URI for OAuth callback (authorization_code flow)
        | Must match exactly what's configured in your Spotify app settings
        */
        'redirect_uri' => env('SPOTIFY_REDIRECT_URI', 'http://localhost:8000/callback'),

        /*
        | Token storage configuration
        */
        'token_storage' => [
            'driver' => env('SPOTIFY_TOKEN_STORAGE', 'file'),
            'path' => storage_path('spotify-tokens.json'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | General API settings and defaults.
    |
    */

    'api' => [
        /*
        | Base URL for Spotify Web API
        */
        'base_url' => 'https://api.spotify.com/v1',

        /*
        | Default pagination limits
        */
        'pagination' => [
            'default_limit' => 20,
            'max_limit' => 50,
        ],

        /*
        | Request timeout in seconds
        */
        'timeout' => 30,

        /*
        | Rate limiting configuration
        */
        'rate_limiting' => [
            'enabled' => true,
            'retry_delay' => 1, // seconds
            'max_retries' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for API responses to improve performance.
    |
    */

    'cache' => [
        'enabled' => env('SPOTIFY_CACHE_ENABLED', true),
        'ttl' => env('SPOTIFY_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => 'spotify_client',
    ],
];