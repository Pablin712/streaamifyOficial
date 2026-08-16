<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaIncidencia extends Model
{
    protected $table = 'cuenta_incidencias';

    protected $fillable = [
        'idcue',
        'servicio_idser',
        'inicio',
        'fin',
        'duracion_minutos',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }
}
