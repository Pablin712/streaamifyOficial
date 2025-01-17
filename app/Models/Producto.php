<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigopro',
        'nombrepro',
        'preciopro',
        'estrellaspro',
        'descripcionpro',
        'foto',
        'tipo_producto_id',
        'categoria_id',
        'activo',
    ];

    // Relación con TipoProducto
    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    // Relación con Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Relación con DetalleProducto
    public function detalles()
    {
        return $this->hasMany(DetalleProducto::class, 'producto_id');
    }
}
