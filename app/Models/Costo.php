<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Costo extends Model
{
    use HasFactory;
    protected $table = 'costos'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idcos'; // Nombre de la clave primaria
    //public $incrementing = false; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = false;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idcos',
        'idcue',
        'fechacos',
        'montocos',
        'descripcioncos'
    ];
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }
}
