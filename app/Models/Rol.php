<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Rol extends Model
{
    use HasFactory;

    protected $table = 'rolesAntes';

    protected $primaryKey = 'idrol';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['idrol', 'detallerol'];

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'idrol', 'idrol');
    }
}
