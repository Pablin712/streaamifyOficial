<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Magis TV / Flujo
 * Maneja hasta 3 perfiles
 */
class Magis extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('magis', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'MAGIS');
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
