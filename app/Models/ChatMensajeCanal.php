<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMensajeCanal extends Model
{
    use HasFactory;

    protected $table = 'chat_mensajes_canal';

    protected $fillable = [
        'idmsg',
        'idconv',
        'contacto_canal_id',
        'canal',
        'direccion',
        'external_message_id',
        'external_thread_id',
        'external_status',
        'media_id',
        'media_url',
        'media_mime_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function mensaje()
    {
        return $this->belongsTo(Mensaje::class, 'idmsg', 'idmsg');
    }

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    public function contacto()
    {
        return $this->belongsTo(ChatContactoCanal::class, 'contacto_canal_id');
    }
}
