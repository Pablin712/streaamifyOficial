<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Prime Video
 * Maneja hasta 6 perfiles
 */
class Prime extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('prime', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'PRIME');
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
