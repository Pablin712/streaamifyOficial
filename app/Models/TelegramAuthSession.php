<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Modelo para gestionar sesiones de autenticación de Telegram
 *
 * Almacena el estado de la conversación durante el proceso de
 * login o registro a través del bot de Telegram
 */
class TelegramAuthSession extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'telegram_auth_sessions';

    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'chat_id',
        'step',
        'proceso',
        'datos',
        'intentos',
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'datos' => 'array',
        'intentos' => 'integer',
        'last_activity' => 'datetime',
    ];

    /**
     * Obtiene o crea una sesión para un chat_id
     */
    public static function obtenerOCrear(int $chatId): self
    {
        return static::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'step' => 'inicio',
                'proceso' => null,
                'datos' => [],
                'intentos' => 0,
            ]
        );
    }

    /**
     * Actualiza el paso actual y los datos
     */
    public function actualizarEstado(string $step, ?string $proceso = null, array $datos = [], int $intentos = 0): void
    {
        $this->update([
            'step' => $step,
            'proceso' => $proceso,
            'datos' => array_merge($this->datos ?? [], $datos),
            'intentos' => $intentos,
            'last_activity' => now(),
        ]);
    }

    /**
     * Actualiza solo los datos sin cambiar el paso
     */
    public function actualizarDatos(array $nuevosDatos): void
    {
        $this->update([
            'datos' => array_merge($this->datos ?? [], $nuevosDatos),
            'last_activity' => now(),
        ]);
    }

    /**
     * Incrementa el contador de intentos
     */
    public function incrementarIntentos(): void
    {
        $this->increment('intentos');
        $this->touch('last_activity');
    }

    /**
     * Reinicia la sesión al estado inicial
     */
    public function reiniciar(): void
    {
        $this->update([
            'step' => 'inicio',
            'proceso' => null,
            'datos' => [],
            'intentos' => 0,
            'last_activity' => now(),
        ]);
    }

    /**
     * Verifica si la sesión está expirada (más de 10 minutos sin actividad)
     */
    public function estaExpirada(): bool
    {
        return $this->last_activity && $this->last_activity->diffInMinutes(now()) > 10;
    }

    /**
     * Scope para sesiones activas (no expiradas)
     */
    public function scopeActivas($query)
    {
        return $query->where('last_activity', '>=', now()->subMinutes(10));
    }

    /**
     * Scope para sesiones expiradas
     */
    public function scopeExpiradas($query)
    {
        return $query->where('last_activity', '<', now()->subMinutes(10));
    }

    /**
     * Limpia sesiones expiradas (puede ejecutarse en un Job programado)
     */
    public static function limpiarExpiradas(): int
    {
        return static::expiradas()->delete();
    }
}
