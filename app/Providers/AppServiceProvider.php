<?php

namespace App\Providers;

use App\Ai\VibeLogger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Events\ToolInvoked;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VibeLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ToolInvoked::class, function (ToolInvoked $event): void {
            $this->app->make(VibeLogger::class)->tool($event);
        });
    }
}
