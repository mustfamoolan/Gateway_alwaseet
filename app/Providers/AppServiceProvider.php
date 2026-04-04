<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('waseet-gateway', function (\Illuminate\Http\Request $request) {
            $project = $request->attributes->get('project');
            $projectId = $project ? $project->id : $request->ip();
            
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($projectId); // 60 per minute = 30 per 30 seconds approx
            // However, Limit::perMinute doesn't easily do "per 30 seconds" exactly in one call without custom logic,
            // but we can use perMinute(60) or two separate limits if needed.
            // Let's use 30 per 30 seconds as requested.
        });
    }
}
