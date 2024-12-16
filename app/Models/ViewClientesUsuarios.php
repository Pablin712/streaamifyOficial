<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewClientesUsuarios extends Model
{
    public $timestamps = false; // No tiene timestamps
    protected $primaryKey = null; // No tiene clave primaria
    protected $table = 'view_clientes_usuarios'; // Nombre de la vista

    // Si quieres seleccionar solo algunas columnas, puedes especificarlas así
    protected $fillable = [
        'idcli', 'nombre_cliente', 'usuarios', 'facturado'
    ];

    // Otra relación, si es necesario, con el modelo "Cliente"
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }
}
