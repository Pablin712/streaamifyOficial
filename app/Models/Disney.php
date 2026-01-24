<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Disney+
 * Maneja hasta 7 perfiles (Premium) o 5 (Standard)
 */
class Disney extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('disney', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->whereIn('idser', ['DISNEYP', 'DISNEYS']);
            });
        });
    }

    /**
     * Verificar si es Premium (7 perfiles) o Standard (5 perfiles)
     */
    public function isPremium()
    {
        return $this->valor && $this->valor->idser === 'DISNEYP';
    }

    public function getMaxPerfiles()
    {
        return $this->isPremium() ? 7 : 5;
    }

    public function scopePremium($query)
    {
        return $query->whereHas('valor', function($q) {
            $q->where('idser', 'DISNEYP');
        });
    }

    public function scopeStandard($query)
    {
        return $query->whereHas('valor', function($q) {
            $q->where('idser', 'DISNEYS');
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
