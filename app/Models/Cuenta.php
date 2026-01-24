<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

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
        'activocue',
        'tipo_cuenta' // 'completa' o 'individual'
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
    public function usuarios() //obtiene los usuarios de la cuenta
    {
        return $this->hasMany(ViewUsuarioActivo::class, 'idcue', 'idcue');
    }
    public function clientes_activos() //obtiene los usuarios activos de la cuenta
    {
        return $this->hasMany(ViewUsuarioActivo::class, 'idcue', 'idcue')
            ->where('fecha_vencimiento', '>', now())->get();
    }
    public function getUsuariosActivosAttribute()
    {
        return ViewUsuarioActivo::where('idcue', $this->idcue)
            ->where('fecha_vencimiento', '>', now())
            ->count();
    }
    public function usuarios_que_siguen()
    {
        return $this->hasMany(ViewUsuarioActivo::class, 'idcue', 'idcue')
            ->where('fecha_vencimiento', '>', $this->fechavencue)->get();
    }
    public function getPagadoMesAttribute()
    {
        return $this->costos()
            ->whereMonth('fechacos', Carbon::now()->month)
            ->whereYear('fechacos', Carbon::now()->year)
            ->sum('montocos');
    }
    public function getCostoMesAttribute()
    {
        $fechaVenc = $this->fechavencue ? Carbon::parse($this->fechavencue) : null;
        if ($fechaVenc && $fechaVenc->month == Carbon::now()->month && $fechaVenc->year == Carbon::now()->year) {
            return $this->valor->costoval;
        } else {
            return 0;
        }
    }
    public function getIsConvenienteRenovarAttribute()
    {
        if ($this->usuarios_que_siguen()->count() >= $this->valor->pantminval) {
            return true;
        } else {
            return false;
        }
    }
}
