<?php

namespace App\Providers;

use App\Models\Service;
use App\Observers\ServiceObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RateLimiter;
use Request;

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

        // Service::observe(ServiceObserver::class);


        // RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
        //     return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        // });

        // // Database Query Logging (for development)
        // if (config('app.debug')) {
        //     DB::listen(function ($query) {
        //         logger(sprintf(
        //             '[DB Query] %s with bindings: %s (%s ms)',
        //             $query->sql,
        //             json_encode($query->bindings),
        //             $query->time
        //         ));
        //     });
        // }

        // // Global Cache Configuration
        // Cache::setDefaultCacheTime(60 * 24); // 24 hours default cache time
    }
}
