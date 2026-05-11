<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Show;
use App\Models\Venue;
use App\Services\SettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
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

        $this->seedProductionDatabaseWhenEmpty();

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

    private function seedProductionDatabaseWhenEmpty(): void
    {
        if (! app()->environment('production') || app()->runningInConsole()) {
            return;
        }

        try {
            if ($this->demoDataIsMissing()) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (Throwable) {
            //
        }
    }

    private function demoDataIsMissing(): bool
    {
        foreach (['categories', 'venues', 'events', 'shows', 'bookings'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return ! Category::query()->exists()
            || ! Venue::query()->exists()
            || ! Event::query()->exists()
            || ! Show::query()->exists()
            || ! Booking::query()->exists();
    }
}
