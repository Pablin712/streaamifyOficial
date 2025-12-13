<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Banco extends Model
{
    use HasFactory;

    protected $table = 'bancos'; // Nombre de la tabla
    protected $primaryKey = 'idban'; // Llave primaria

    protected $fillable = [
        'nombreban',
        'propietarioban',
        'cedulaban',
        'numeroban',
        'tipoban',
        'detalleban',
        'foto',
        'monto',
    ];

    // Relación con Recarga (un banco puede tener muchas recargas asociadas)
    public function recargas()
    {
        return $this->hasMany(Recarga::class, 'idban', 'idban');
    }

    // Relación con Transaccion (un banco puede tener muchas transacciones)
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'banco_id', 'idban');
    }
}
