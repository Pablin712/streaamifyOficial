<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Callback global: se ejecuta antes de cualquier otra comprobación de autorización
        Gate::before(function ($user, $ability) {
            // Asumiendo que el nombre del permiso coincide con la habilidad (ability)
            if ($user->hasPermissionTo($ability)) {
                return true;
            }
        });
    }
}
