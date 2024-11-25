<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Servicio extends Model
{
    use HasFactory;
    protected $table = 'servicios'; //encargado de administrar la tabla ...

    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idser',
        'nombreser',
        'completoser',
        'precioser',
        'comboser',
        'reventaser',
        'revcompser'
    ];
}
