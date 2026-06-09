<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; 
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
        // 2. Ini ditambahkan agar jika diakses lewat ngrok, CSS otomatis berubah jadi https
        if (str_contains(request()->url(), 'ngrok-free')) {
            URL::forceScheme('https');
        }
    }
}