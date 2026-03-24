<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        try {
            $settings = \App\Models\SiteSetting::allKeyed();
            View::share('siteSettings', $settings);

            // Override app.name so notifications, mail subjects, etc. use the DB site name
            if (!empty($settings['site_name'])) {
                config(['app.name' => $settings['site_name']]);
            }
        } catch (\Exception $e) {
            View::share('siteSettings', []);
        }
    }
}
