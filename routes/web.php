<?php

use App\Http\Controllers\VibeController;
use Illuminate\Support\Facades\Route;

// Vibe PHP: every request is served by reading the matching PHP script and
// letting an AI "execute" it. The catch-all must stay last.
Route::any('/{path?}', VibeController::class)
    ->where('path', '.*');
