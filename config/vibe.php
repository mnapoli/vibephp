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

];
