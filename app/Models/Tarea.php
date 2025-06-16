<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombretarea',
        'descripcion',
        'prioridad',
        'completada',
        'fechalimit',
        'completada_por',
        'fecha_completada'
    ];
    protected $casts = [
        'completada' => 'boolean',
        'fechalimit' => 'datetime',
        'fecha_completada' => 'datetime',
    ];
}
