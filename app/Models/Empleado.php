<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class Empleado extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;
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
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'empleado_id', 'idemp');
    }

    /**
     * Relación con la tabla de roles (Muchos a Muchos)
     */
    // 🚀 **Usa el método de Spatie para la relación roles**
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'completada_por', 'idemp');
    }
    public function getNumTareasCompletadasAttribute()
    {
        return $this->tareas()->where('completada', true)->count();
    }

    /**
     * Verifica si el empleado tiene un rol específico.
     */
    public function tieneRol($rol)
    {
        return $this->hasRole($rol);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
