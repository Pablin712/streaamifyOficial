<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['idcli', 'producto_id', 'estado', 'fechapedido', 'respuesta'];
    public $timestamps = false;
    protected $casts = [
        'fechapedido' => 'datetime', // Convierte automáticamente en objeto Carbon
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli');
    }
    public function estado()
    {
        return $this->belongsTo(EstadoRecarga::class, 'idestado');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
