<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMemoriaNegocio extends Model
{
    use HasFactory;

    protected $table = 'chat_memoria_negocio';

    protected $fillable = [
        'tipo',
        'clave',
        'titulo',
        'contenido',
        'resumen',
        'visibilidad',
        'tags',
        'fuente',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'tags' => 'array',
        'activo' => 'boolean',
    ];
}
