<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    public $timestamps = false; // Desactiva timestamps automáticos
    protected $table = 'asistencias'; // Nombre de la tabla
    protected $fillable = [
        'empleado_id',
        'ruta_actual',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     * Laravel automáticamente convierte entre UTC (BD) y timezone de la app
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'idemp');
    }
}
