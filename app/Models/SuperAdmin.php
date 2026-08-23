<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Cuenta de Pablo/su equipo para el panel central (gestion de Tenants).
 * Vive SIEMPRE en la BD central (streamify_central), nunca en la de un
 * Tenant — por eso fija su propia conexion en vez de heredar la default,
 * que stancl/tenancy reescribe por tenant en runtime.
 */
class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $connection = 'central';
    protected $table = 'super_admins';

    protected $fillable = [
        'nombre',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
