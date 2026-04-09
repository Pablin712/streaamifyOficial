<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMemoriaContacto extends Model
{
    use HasFactory;

    protected $table = 'chat_memoria_contactos';

    protected $fillable = [
        'contacto_canal_id',
        'idcli',
        'tipo',
        'clave',
        'valor_texto',
        'valor_json',
        'origen',
        'confianza',
        'vigente_hasta',
        'ultima_referencia_at',
    ];

    protected $casts = [
        'valor_json' => 'array',
        'vigente_hasta' => 'datetime',
        'ultima_referencia_at' => 'datetime',
    ];

    public function contacto()
    {
        return $this->belongsTo(ChatContactoCanal::class, 'contacto_canal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }
}
