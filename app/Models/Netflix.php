<?php

namespace App\Models;

/**
 * Modelo especializado para cuentas de Netflix
 * Maneja hasta 5 perfiles con PINs específicos
 */
class Netflix extends Cuenta
{
    protected $table = 'cuentas';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('netflix', function ($builder) {
            $builder->whereHas('valor.servicio', function($q) {
                $q->where('idser', 'NETFLIX');
            });
        });
    }

    /**
     * Obtener perfiles con PINs predefinidos
     */
    public function getPerfilesConPin()
    {
        return $this->perfiles()->get()->map(function($perfil) {
            return [
                'numero' => $perfil->numeroper,
                'pin' => $perfil->pinper,
                'usuario' => $this->usuariocue,
                'contrasena' => $this->contrasenacue,
                'disponible' => !$perfil->detalles_venta()
                    ->where('activodet', true)
                    ->where('fechavendet', '>', now())
                    ->exists()
            ];
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
