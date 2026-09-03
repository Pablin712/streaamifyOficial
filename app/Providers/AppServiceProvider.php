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
use App\Services\AparienciaService;
use Dedoc\Scramble\Scramble;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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

        // Apariencia global (tema + modo oscuro) disponible en TODAS las vistas,
        // publicas y del panel, para que los layouts la pinten en el <html> del
        // lado del servidor. Asi el tema que elige el administrador llega a todos
        // los dispositivos y sesiones, y no hay parpadeo al cargar.
        // Es perezoso: el servicio solo consulta la BD cuando la vista lo usa.
        View::composer('*', function ($view) {
            static $apariencia = null;
            $apariencia ??= app(AparienciaService::class)->paraVista();
            $view->with('apariencia', $apariencia);
        });

        Scramble::configure()->expose('docs/clavesegura/api', 'docs/clavesegura/api.json');
    }
}
