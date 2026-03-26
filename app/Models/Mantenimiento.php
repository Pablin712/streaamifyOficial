<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Mantenimiento extends Model
{
    //
    use HasFactory;

    protected $table = 'mantenimientos'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'idman'; // Clave primaria personalizada

    public $timestamps = false; // Si no tienes columnas created_at/updated_at

    protected $fillable = [
        'idcue',
        'idcue_snapshot',
        'idval_snapshot',
        'servicio_snapshot',
        'cuenta_usuario_snapshot',
        'descripcionman',
        'fechaman',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }
}
