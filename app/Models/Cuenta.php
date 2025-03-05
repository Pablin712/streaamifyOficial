<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Cuenta extends Model
{
    use HasFactory;
    protected $table = 'cuentas'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idcue'; // Nombre de la clave primaria
    //public $incrementing = false; // Si no es incremental, establece esto en false
    protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idcue',
        'idval',
        'fechavencue',
        'usuariocue',
        'contrasenacue',
        'caidacue',
        'activocue'
    ];
    public function valor()
    {
        return $this->belongsTo(Valor::class, 'idval', 'idval');
    }
    public function costos()
    {
        return $this->hasMany(Costo::class, 'idcue', 'idcue');
        //->onDelete('cascade');
    }
    public function perfiles()
    {
        return $this->hasMany(Perfil::class, 'idcue', 'idcue');
    }
    public function mantenimiento()
    {
        return $this->hasOne(Mantenimiento::class, 'idcue', 'idcue');
    }
}
