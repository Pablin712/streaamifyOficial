<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['idcli', 'producto_id', 'estado', 'fechapedido'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
