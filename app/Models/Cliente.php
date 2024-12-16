<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idcli'; // Nombre de la clave primaria
    public $incrementing = true; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'nombrecli',
        'telefonocli'
    ];
}
