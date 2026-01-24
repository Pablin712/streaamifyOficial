<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de HBO Max
 * Maneja hasta 5 perfiles
 */
class Max extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('max', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'MAX');
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
