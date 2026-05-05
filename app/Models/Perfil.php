<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Perfil extends Model
{
    use HasFactory;
    protected $table = 'perfiles'; //encargado de administrar la tabla ...

    protected $primaryKey = 'idper'; // Nombre de la clave primaria
    public $incrementing = false; // Si no es incremental, establece esto en false
    protected $keyType = 'string'; // Si es de tipo string, define esto como 'string'
    public $timestamps = false;

    // Define los atributos que puedes asignar masivamente
    protected $fillable = [
        'idper',
        'idcue',
        'numeroper',
        'pinper'
    ];
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }
    public function usuarios()
    {
        return $this->hasManyThrough(
            ViewUsuarioActivo::class, // Modelo destino
            DetalleVenta::class,      // Modelo intermedio
            'idper',    // Foreign key en DetalleVenta
            'iddet',    // Foreign key en ViewUsuarioActivo
            'idper',    // Local key en Perfil
            'iddet'     // Local key en DetalleVenta
        );
    }
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'idper', 'idper')->onDelete('cascade');
    }
    public function getNumUsuariosAttribute()
    {
        $usuariosActivos = ViewUsuarioActivo::where('perfil', $this->numeroper)
                ->where('idcue', $this->idcue)
                ->count();
        return $usuariosActivos;
    }
}
