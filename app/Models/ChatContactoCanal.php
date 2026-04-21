<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatContactoCanal extends Model
{
    use HasFactory;

    protected $table = 'chat_contactos_canal';

    protected $fillable = [
        'canal',
        'canal_user_id',
        'telefono_normalizado',
        'nombre_canal',
        'idcli',
        'estado_relacion',
        'origen',
        'metadata',
        'last_seen_at',
        'id_agente_asignado',
        'telefono',
        'nombre',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_seen_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class, 'canal_contacto_id');
    }

    public function memorias()
    {
        return $this->hasMany(ChatMemoriaContacto::class, 'contacto_canal_id');
    }

    public function resumenes()
    {
        return $this->hasMany(ChatMemoriaResumen::class, 'contacto_canal_id');
    }

    // NUEVAS RELACIONES
    public function mensajes()
    {
        return $this->hasMany(ChatMensajeCanal::class, 'id_contacto_canal');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(ChatMensajeCanal::class, 'id_contacto_canal')
            ->latest();
    }

    public function agenteAsignado()
    {
        return $this->belongsTo(User::class, 'id_agente_asignado');
    }
}
