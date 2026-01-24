<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Crunchyroll
 * Maneja hasta 5 perfiles
 */
class Crunchyroll extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('crunchyroll', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'CRUNCHY');
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
