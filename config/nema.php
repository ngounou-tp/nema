<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | When every AI provider is unavailable, demo mode answers with the
    | closest prepared response from storage/app/demo/fallbacks.json
    | instead of an error, so the jury demo never stops on a blank screen.
    |
    */

    'demo_mode' => (bool) env('NEMA_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | AI layer
    |--------------------------------------------------------------------------
    |
    | Provider keys and models live in config/services.php. `?:` keeps the
    | defaults when a variable is present but empty in .env.
    |
    */

    'ai' => [
        // Text providers, tried in this order (groq, openrouter, gemini).
        'text_providers' => env('AI_TEXT_PROVIDERS') ?: 'groq,openrouter',
        'max_tokens' => (int) (env('AI_MAX_TOKENS') ?: 800),
        // Gemini counts its "thinking" tokens in the output budget: keep room for the JSON.
        'vision_max_tokens' => (int) (env('AI_VISION_MAX_TOKENS') ?: 4096),
        'daily_limit_per_user' => (int) (env('AI_DAILY_LIMIT_PER_USER') ?: 30),
        'per_minute_per_user' => 10,
        'timeout' => 30,
        'vision_timeout' => 45,
        'retries' => 1,
        'cache_ttl' => 60 * 60 * 24,
        'log_channel' => env('AI_LOG_CHANNEL') ?: 'ai',
        'fallbacks_path' => storage_path('app/demo/fallbacks.json'),
    ],

];
