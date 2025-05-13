<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idpro'; // Nombre de la clave primaria

    //public $incrementing = false; // Si no es incremental, establece esto en false
    //protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = true;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'nombrepro',
        'telefonopro',
        'activopro'
    ];
    public function valores()
    {
        return $this->hasMany(Valor::class, 'idpro', 'idpro');
    }
    public function cuentas()
    {
        return $this->hasManyThrough(
            Cuenta::class,  // Modelo final al que queremos acceder (Cuentas)
            Valor::class,   // Modelo intermedio (Valores)
            'idpro',        // Clave foránea en la tabla intermedia (Valores) que referencia a Proveedores
            'idval',        // Clave foránea en la tabla final (Cuentas) que referencia a Valores
            'idpro',        // Clave local en la tabla Proveedores
            'idval'         // Clave local en la tabla Valores
        )->where('activocue', true);
    }
    public function cuentasPorServicio($idser)
    {
        return $this->cuentas()->whereHas('valor.servicio', function ($query) use ($idser) {
            $query->where('idser', $idser);
        });
    }
    public function contarCuentasPorServicio($idser)
    {
        return $this->cuentas()->whereHas('valor.servicio', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })->count();
    }
}
