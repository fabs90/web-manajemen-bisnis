<?php

namespace App\Providers;

use App\Enum\Feature;
use App\Models\User;
use App\Services\Subscription\EntitlementService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(EntitlementService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip())->response(function () {
                return back()->with('error', 'Terlalu banyak percobaan masuk yang salah. Silakan coba lagi dalam beberapa saat.');
            });

        });
        Gate::define('feature', fn (User $user, Feature $feature) => $user->hasFeature($feature));
    }
}
