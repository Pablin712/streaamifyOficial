<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TipoGasto extends Model
{
    use HasFactory;

    protected $table = 'tipo_gasto'; // Nombre de la tabla

    protected $primaryKey = 'idtip'; // Clave primaria de la tabla

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'detalletip', // Detalle del tipo de gasto
        'excluir_de_ganancia', // Si es true, no cuenta como gasto operativo (ej: retiro/sueldo del dueño, nómina)
    ];

    protected $casts = [
        'excluir_de_ganancia' => 'boolean',
    ];

    // Relación con los gastos
    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'idtip');
    }
}
