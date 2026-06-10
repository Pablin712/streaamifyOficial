<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChatAgenteImagen extends Model
{
    protected $table = 'chat_agente_imagenes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'archivo',
        'mime_type',
        'tags',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'tags'   => 'array',
        'activo' => 'boolean',
    ];

    public function getUrlPublicaAttribute(): string
    {
        return Storage::disk('public')->url('agente/' . $this->archivo);
    }

    public function getRutaAbsolutaAttribute(): string
    {
        return Storage::path('public/agente/' . $this->archivo);
    }
}
