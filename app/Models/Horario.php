<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'empleado_id', 'fecha', 'hora_inicio', 'hora_fin',
        'notas', 'creado_por', 'cancelado',
    ];

    protected $casts = [
        'fecha'     => 'date',
        'cancelado' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'idemp');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Empleado::class, 'creado_por', 'idemp');
    }
}
