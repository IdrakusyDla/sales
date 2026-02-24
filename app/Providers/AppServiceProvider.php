<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Pastikan baris ini ada

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Jangan paksa HTTPS di development
        // Hanya aktifkan di production jika benar-benar perlu
        // if($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }
        
        // Load helper functions
        require_once app_path('Helpers/StorageHelper.php');
    }
}