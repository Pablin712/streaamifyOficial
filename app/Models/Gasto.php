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
        'transaccion_id', // Transacción asociada
    ];

    // Relación con el modelo TipoGasto
    public function tipoGasto()
    {
        return $this->belongsTo(TipoGasto::class, 'idtip');
    }

    public function transaccion()
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id', 'id');
    }

    // Acceso al banco a través de la transacción
    public function banco()
    {
        return $this->hasOneThrough(
            Banco::class,
            Transaccion::class,
            'id',        // FK en transacciones
            'idban',     // FK en bancos
            'transaccion_id', // Local key en gastos
            'banco_id'   // Local key en transacciones
        );
    }
}
