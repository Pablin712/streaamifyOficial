<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Paramount+
 * Maneja hasta 6 perfiles
 */
class Paramount extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('paramount', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'PARAMOUNT');
            });
        });
    }

    public function scopeCompletas($query)
    {
        return $query->where('idcue', 'NOT LIKE', 'IND.%');
    }

    public function scopeIndividuales($query)
    {
        return $query->where('idcue', 'LIKE', 'IND.%');
    }
}
