<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    use HasFactory;

    protected $table = 'historial';

    protected $fillable = [
        'accion',
        'descripcion',
        'empleado_id',
        'idcli',
        'iddet',
    ];

    // Deshabilitar el manejo de updated_at
    const UPDATED_AT = null;

    // Relación con el modelo Empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
