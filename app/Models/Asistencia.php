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

    protected $casts = [
        'created_at' => 'datetime',
    ];    

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'idemp');
    }
}
