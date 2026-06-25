<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VibePHP Docroot
    |--------------------------------------------------------------------------
    |
    | The directory containing the PHP scripts that the VibePHP "runtime" will
    | read and (creatively) interpret. Think of it as the document root of an
    | old-school PHP site: drop an index.php in here and visit the app.
    |
    */

    'docroot' => env('VIBE_DOCROOT', base_path('vibe')),

    /*
    |--------------------------------------------------------------------------
    | Model Override
    |--------------------------------------------------------------------------
    |
    | Optionally pin the model used to interpret scripts. When null, the AI
    | SDK's default model for the configured provider is used. The provider
    | itself is set on the App\Ai\Agents\VibePhpRuntime agent.
    |
    */

    'model' => env('VIBE_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    |
    | Each "executed" request can log a pretty summary line — timing, tool
    | calls, tokens, and estimated cost. Lines are written to stderr (so they
    | show up live under `php artisan serve` / `php artisan vibe`) and, when a
    | path is set, appended to a log file for history.
    |
    */

    'log' => [
        'enabled' => env('VIBE_LOG', true),
        'console' => env('VIBE_LOG_CONSOLE', true),
        'file' => env('VIBE_LOG_FILE', storage_path('logs/vibe.log')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Pricing
    |--------------------------------------------------------------------------
    |
    | Used only to estimate the cost of each request — the AI SDK does not
    | report cost. Prices are in US dollars per 1,000,000 tokens. Adjust these
    | to match your provider's current pricing. Unknown models log "$?".
    |
    | @var array<string, array{input: float, output: float, cached_input?: float}>
    */

    'pricing' => [
        'gpt-5.4' => ['input' => 1.25, 'output' => 10.00, 'cached_input' => 0.125],
        'gpt-5' => ['input' => 1.25, 'output' => 10.00, 'cached_input' => 0.125],
    ],

];
