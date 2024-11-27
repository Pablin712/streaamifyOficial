<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Gasto extends Model
{
    use HasFactory;

    protected $table = 'gastos'; // Nombre de la tabla

    protected $primaryKey = 'idgas'; // Clave primaria de la tabla

    // Definir los campos que se pueden llenar masivamente
    protected $fillable = [
        'idtip',          // Tipo de Gasto
        'fechagas',       // Fecha del Gasto
        'montogas',       // Monto del Gasto
        'descripciongas', // Descripción del Gasto
    ];

    // Relación con el modelo TipoGasto
    public function tipoGasto()
    {
        return $this->belongsTo(TipoGasto::class, 'idtip');
    }
}
