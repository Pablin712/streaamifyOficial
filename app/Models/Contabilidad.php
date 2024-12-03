<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Contabilidad extends Model
{
    use HasFactory;

    // Tabla asociada
    protected $table = 'contabilidad';

    // Clave primaria personalizada
    protected $primaryKey = 'idcon';

    // Indica si la clave primaria es auto-incremental
    public $incrementing = true;

    // Tipo de clave primaria
    protected $keyType = 'int';

    // Desactiva las marcas de tiempo si no las usas
    public $timestamps = false;

    // Atributos que se pueden asignar en masa
    protected $fillable = [
        'mes',
        'año',
        'detalle',
        'num_cuentas',
        'num_usuarios',
        'ingresos',
        'costos',
        'ganancias',
        'renta',
    ];

    public function setMesAttribute($value)
    {
        $this->attributes['mes'] = $value ?? Carbon::now()->month;
    }

    public function setAñoAttribute($value)
    {
        $this->attributes['año'] = $value ?? Carbon::now()->year;
    }
}
