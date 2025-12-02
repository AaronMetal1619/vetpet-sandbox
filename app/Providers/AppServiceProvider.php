<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan; // <--- Importante
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // <--- ESTA ES LA LÍNEA QUE FALTA

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
        // Fix para longitud de strings en MySQL viejos (común en servidores compartidos)
        Schema::defaultStringLength(191);

        // 🔥 LA SOLUCIÓN NUCLEAR 🔥
        // Esto obliga a Laravel a borrar su caché de rutas CADA VEZ que se usa.
        // No es lo ideal para una app gigante, pero para tu proyecto escolar en Render 
        // es la solución perfecta para evitar estos errores 404.
        try {
            Artisan::call('route:clear');
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Silenciar errores si ocurren durante el boot
        }
        // Si estamos en producción (Render), forzamos HTTPS
    if($this->app->environment('production')) {
        URL::forceScheme('https');
    }
    }
}