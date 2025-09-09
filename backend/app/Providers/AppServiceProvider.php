<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
        // Устанавливаем русскую локаль для дат
        Carbon::setLocale('ru');
        
        // Устанавливаем часовой пояс Таджикистана
        config(['app.timezone' => 'Asia/Dushanbe']);
        date_default_timezone_set('Asia/Dushanbe');
    }
}
