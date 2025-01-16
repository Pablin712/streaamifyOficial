<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Empleado extends Authenticatable
{
    use Notifiable;

    protected $table = 'empleados'; // Nombre de la tabla en tu base de datos.

    protected $primaryKey = 'idemp'; // La clave primaria de tu tabla.

    /**
     * Indica si la clave primaria es autoincremental.
     *
     * @var bool
     */
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true; // Cambiar a `false` si no quieres que se usen.

    protected $fillable = [
        'nombreemp',     // Nombre del empleado
        'telefonoemp',   // Teléfono del empleado
        'usuarioemp',    // Usuario del empleado
        'passwordemp',   // Contraseña hasheada
        'idrol', 
        'foto_url',
        'email'      // Agregar esta columna para permitir asignación masiva
    ];

   
    protected $hidden = [
        'passwordemp', // Ocultar la contraseña en las respuestas JSON.
    ];

    public function setPasswordempAttribute($value)
    {
        $this->attributes['passwordemp'] = bcrypt($value);
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idemp', 'idemp');
    }
}
