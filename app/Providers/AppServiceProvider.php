<?php

namespace App\Providers;

use App\Http\Controllers\PrinterSettingController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
        Schema::defaultStringLength(191);

        if ((bool) env('APP_FORCE_HTTPS', false) || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $company = PrinterSettingController::companySettings();
            $view->with('appCompanyName', trim((string) ($company['name'] ?? '')) ?: 'UTE Parts');
        });
    }
}
