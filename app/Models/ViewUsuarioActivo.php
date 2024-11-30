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
        'idcli', 'nombre_cliente', 'idcue', 'perfil', 'fecha_vencimiento'
    ];

    // Si se desea, también puedes establecer relaciones con otros modelos
    // Si la vista tiene una relación, por ejemplo con un modelo "Cuenta"
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'IDCUE', 'idcue');
    }

    // Otra relación, si es necesario, con el modelo "Cliente"
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCLI', 'idcli');
    }
}
