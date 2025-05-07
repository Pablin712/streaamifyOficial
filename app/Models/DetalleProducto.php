<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DetalleProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'idser',
        'descripcion',
        'meses',
    ];

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idser', 'idser');
    }
}
