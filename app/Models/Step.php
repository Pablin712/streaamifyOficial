<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para gestionar los pasos del flujo de autenticación
 *
 * Cada paso contiene instrucciones detalladas para el agente IA
 */
class Step extends Model
{
    protected $table = 'steps';

    protected $fillable = [
        'name',
        'description',
        'next_step',
    ];

    /**
     * Obtener un paso específico por nombre
     */
    public static function obtenerPorNombre(string $nombre): ?self
    {
        return static::where('name', $nombre)->first();
    }

    /**
     * Obtener todos los pasos ordenados
     */
    public static function obtenerTodos(): array
    {
        return static::orderBy('id')->get()->toArray();
    }
}
