<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Valor extends Model
{
    use HasFactory;
    protected $table = 'valores'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idval'; // Nombre de la clave primaria

    //public $incrementing = false; // Si no es incremental, establece esto en false
    protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idval',
        'idser',
        'idpro',
        'costoval',
        'pantminval',
        'pantmaxval',
        'mesesval'
    ];
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idser', 'idser');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idpro', 'idpro');
    }
}