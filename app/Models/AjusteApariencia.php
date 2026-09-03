<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Apariencia global de la plataforma (tabla de una sola fila).
 *
 * No se consulta directo desde controladores ni vistas: usa
 * App\Services\AparienciaService, que cachea y resuelve el tema efectivo.
 */
class AjusteApariencia extends Model
{
    protected $table = 'ajustes_apariencia';

    protected $fillable = [
        'tema',
        'modo_oscuro',
        'auto_temporada',
        'actualizado_por',
    ];

    protected $casts = [
        'modo_oscuro'    => 'boolean',
        'auto_temporada' => 'boolean',
    ];
}
