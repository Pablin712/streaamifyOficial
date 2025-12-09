<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickResponse extends Model
{
    protected $fillable = [
        'comando',
        'titulo',
        'contenido',
        'tipo',
        'activo',
        'orden',
        'tags',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'tags' => 'array',
    ];

    /**
     * Scope para filtrar solo respuestas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where(function($q) use ($tipo) {
            $q->where('tipo', $tipo)
              ->orWhere('tipo', 'ambos');
        });
    }

    /**
     * Buscar por comando exacto
     */
    public function scopePorComando($query, $comando)
    {
        // Normalizar comando (quitar / si existe)
        $comandoNormalizado = ltrim($comando, '/');
        return $query->where('comando', $comandoNormalizado);
    }

    /**
     * Buscar por término en comando, título o tags
     */
    public function scopeBuscar($query, $termino)
    {
        $termino = ltrim($termino, '/');
        return $query->where(function($q) use ($termino) {
            $q->where('comando', 'LIKE', "%{$termino}%")
              ->orWhere('titulo', 'LIKE', "%{$termino}%")
              ->orWhereJsonContains('tags', $termino);
        });
    }
}
