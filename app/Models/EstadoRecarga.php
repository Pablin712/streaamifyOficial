<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EstadoRecarga extends Model
{
    use HasFactory;

    protected $table = 'estado_recargas'; // Nombre de la tabla
    protected $primaryKey = 'idestado'; // Llave primaria

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con Recarga (un estado puede estar asociado con muchas recargas)
    public function recargas()
    {
        return $this->hasMany(Recarga::class, 'idestado', 'idestado');
    }
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'idestado', 'idestado');
    }
}
