<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        Paginator::useTailwind();

        try {
            if (Schema::hasTable('settings')) {
                $settings = app(SettingsService::class)->grouped();

                config([
                    'app.name' => data_get($settings, 'general.site_name', config('app.name')),
                ]);

                View::share('appSettings', $settings);
            }
        } catch (Throwable) {
            View::share('appSettings', []);
        }
    }
}
