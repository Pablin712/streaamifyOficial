<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soporte extends Model
{
    use HasFactory;

    public const TIPOS = [
        'sin suscripcion',
        'contrasena incorrecta',
        'muchos dispositivos',
        'otro',
    ];

    public const ESTADOS = [
        'pendiente',
        'atendido',
    ];

    protected $table = 'soportes';

    protected $primaryKey = 'idsop';

    protected $fillable = [
        'idcli',
        'idcue',
        'tipo',
        'descripcion',
        'solucion',
        'estado',
        'whatsapp_enviado_at',
    ];

    protected $casts = [
        'whatsapp_enviado_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'idcue', 'idcue');
    }
}
