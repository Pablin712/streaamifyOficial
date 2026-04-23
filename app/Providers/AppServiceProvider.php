<?php

namespace App\Providers;

use App\Models\Costo;
use App\Models\Gasto;
use App\Models\Venta;
use App\Observers\CostoObserver;
use App\Observers\GastoObserver;
use App\Observers\VentaObserver;
use Illuminate\Support\Facades\Gate;
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

        Gate::define('viewApiDocs', function () {
            $token = (string) config('app.api_docs_token', '');
            $provided = (string) request()->header('X-Docs-Token', '');

            return $token !== '' && hash_equals($token, $provided);
        });
    }
}
