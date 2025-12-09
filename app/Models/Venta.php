<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idven'; // Nombre de la clave primaria
    protected $casts = [
        'fechaven' => 'datetime',
    ];
    public $incrementing = false; // No es auto-incremental, lo genera el trigger MySQL
    protected $keyType = 'string'; // El idven es VARCHAR(20) formato: 001-001-000000001
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idemp',
        'idcli',
        'fechaven',
    ];
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'idemp', 'idemp');
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }
    public function detalles_venta()
    {
        return $this->hasMany(DetalleVenta::class, 'idven', 'idven');
    }
    public function usuarios()
    {
        return $this->hasMany(ViewUsuarioActivo::class, 'idven', 'idven');
    }
}
