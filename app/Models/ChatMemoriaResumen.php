<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMemoriaResumen extends Model
{
    use HasFactory;

    protected $table = 'chat_memoria_resumenes';

    protected $fillable = [
        'idconv',
        'contacto_canal_id',
        'idcli',
        'subagente_id',
        'tipo',
        'ventana_desde',
        'ventana_hasta',
        'resumen',
        'hechos_clave',
        'expira_at',
    ];

    protected $casts = [
        'ventana_desde' => 'datetime',
        'ventana_hasta' => 'datetime',
        'hechos_clave' => 'array',
        'expira_at' => 'datetime',
    ];

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    public function contacto()
    {
        return $this->belongsTo(ChatContactoCanal::class, 'contacto_canal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    public function subagente()
    {
        return $this->belongsTo(ChatSubagente::class, 'subagente_id');
    }
}
