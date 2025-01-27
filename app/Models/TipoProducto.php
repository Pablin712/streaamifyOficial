<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TipoProducto extends Model
{
    use HasFactory;

    protected $table = 'tipos_producto'; // Asegúrate de usar el nombre correcto
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con Producto
    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_producto_id', 'id');
    }
}
