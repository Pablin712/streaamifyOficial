<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DetalleVenta extends Model
{
    use HasFactory;
    protected $table = 'detalles_venta'; //encargado de administrar la tabla ...

    protected $primaryKey = 'iddet'; // Nombre de la clave primaria
    //public $incrementing = false; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;
    protected $casts = [
        'fechavendet' => 'datetime', // Convierte fechavendet automáticamente en Carbon
    ];
    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idven',
        'idper',
        'descripciondet',
        'fechavendet',
        'montodet',
        'activodet', // SÍ, NO
        'estado', // PENDIENTE, COBRADO
    ];
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idven', 'idven');
    }
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idper', 'idper');
    }

    // Relación directa a servicio a través de perfil -> cuenta -> valor
    public function servicio()
    {
        return $this->hasOneThrough(
            Servicio::class,
            Perfil::class,
            'idper', // FK en perfiles
            'idser', // FK en servicios
            'idper', // Local key en detalles_venta
            'idser'  // Local key en perfiles (a través de cuenta -> valor)
        );
    }
}
