<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Costo extends Model
{
    use HasFactory;
    protected $table = 'costos'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idcos'; // Nombre de la clave primaria
    //public $incrementing = false; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = false;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idcue',
        'idcue_snapshot',
        'idval_snapshot',
        'servicio_snapshot',
        'cuenta_usuario_snapshot',
        'fechacos',
        'montocos',
        'descripcioncos',
        'transaccion_id',
    ];
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
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
            'transaccion_id', // Local key en costos
            'banco_id'   // Local key en transacciones
        );
    }

    // Relación con deuda
    public function deuda()
    {
        return $this->hasOne(Deuda::class, 'costo_id', 'idcos');
    }
}
