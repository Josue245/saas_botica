<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Services\TenantManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, fn() => new TenantManager());
        //
    }

    public function boot(): void
    {
        // Rate limiting: max 5 intentos de login por minuto por IP
        RateLimiter::for("login", function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Evita errores de longitud de índice en MySQL < 5.7.7
        Schema::defaultStringLength(191);
        Paginator::useTailwind();

        // Comparte el contador de alertas con el menú lateral y la barra superior
        View::composer(['partials.sidebar', 'partials.topbar'], function ($view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;
            $count = 0;
            try {
                if (Schema::hasTable('productos')) {
                    $hoy = Carbon::today();
                    $stockBajo = DB::table('productos')->whereColumn('stock', '<=', 'stock_minimo')->count();
                    $porVencer = DB::table('productos')
                        ->whereNotNull('fecha_vencimiento')
                        ->whereBetween('fecha_vencimiento', [$hoy, (clone $hoy)->addDays(60)])
                        ->count();
                    $vencidos = DB::table('productos')
                        ->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<', $hoy)
                        ->count();
                    $count = $stockBajo + $porVencer + $vencidos;
                }
            } catch (\Throwable $e) {
                $count = 0;
            }
            $view->with('alertasCount', $count);
        });
    }
}
