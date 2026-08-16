<?php

namespace App\Providers;

use App\Models\Costo;
use App\Models\Cuenta;
use App\Models\Gasto;
use App\Models\Venta;
use App\Observers\CostoObserver;
use App\Observers\CuentaObserver;
use App\Observers\GastoObserver;
use App\Observers\VentaObserver;
use Dedoc\Scramble\Scramble;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        Venta::observe(VentaObserver::class);
        Costo::observe(CostoObserver::class);
        Gasto::observe(GastoObserver::class);
        Cuenta::observe(CuentaObserver::class);

        // El resto de las vistas admin usan Bootstrap; el partial de paginacion por
        // defecto de Laravel trae SVG con clases de Tailwind (h-5 w-5) que no existen
        // aca, asi que los iconos de flecha se ven gigantes. Bootstrap 5 usa markup
        // <ul class="pagination"> plano, ya estilizado en enhanced-table-global.css.
        Paginator::useBootstrapFive();

        Scramble::configure()->expose('docs/clavesegura/api', 'docs/clavesegura/api.json');
    }
}
