<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewUsuarioActivo extends Model
{
    // La vista no tiene una clave primaria, así que desactivamos la propiedad 'primaryKey'
    public $timestamps = false; // No tiene timestamps
    protected $primaryKey = null; // No tiene clave primaria
    protected $table = 'view_usuarios_activos'; // Nombre de la vista

    // Si quieres seleccionar solo algunas columnas, puedes especificarlas así
    protected $fillable = [
        'idcli',
        'nombre_cliente',
        'idven',
        'iddet',
        'idcue',
        'perfil',
        'fecha_vencimiento'
    ];

    // Si se desea, también puedes establecer relaciones con otros modelos
    // Si la vista tiene una relación, por ejemplo con un modelo "Cuenta"
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }

    // Otra relación, si es necesario, con el modelo "Cliente"
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }
    // Otra relación, si es necesario, con el modelo "Venta"
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idven', 'idven');
    }
    public function detalle_venta()
    {
        return $this->belongsTo(DetalleVenta::class, 'iddet', 'iddet');
    }
    public function profile()
    {
        return $this->hasOneThrough(
            Perfil::class,         // Modelo destino
            DetalleVenta::class,   // Modelo intermedio
            'iddet',               // Foreign key en DetalleVenta (relaciona con ViewUsuarioActivo)
            'idper',               // Foreign key en Perfil (relaciona con DetalleVenta)
            'iddet',               // Local key en ViewUsuarioActivo
            'idper'                // Local key en DetalleVenta
        );
    }
}
