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
        // Provide a null default so app('tenant') never throws outside the
        // request lifecycle (artisan, queue jobs, seeders). TenantMiddleware
        // overrides this per-request via app()->instance('tenant', ...).
        $this->app->scoped('tenant', fn () => null);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
