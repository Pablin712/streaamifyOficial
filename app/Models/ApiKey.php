<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key',
        'empleado_id',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active',
        'ip_whitelist',
        'requests_count',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key', // Ocultar en respuestas JSON por defecto
    ];

    /**
     * Relación con empleado propietario
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'idemp');
    }

    /**
     * Generar nueva API Key
     */
    public static function generate(string $name, ?int $empleadoId = null, array $permissions = [], ?Carbon $expiresAt = null)
    {
        return self::create([
            'name' => $name,
            'key' => 'sk_' . Str::random(60), // Prefijo "sk_" + 60 caracteres aleatorios
            'empleado_id' => $empleadoId,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    /**
     * Verificar si la API Key está vigente
     */
    public function isValid(): bool
    {
        // Verificar si está activa
        if (!$this->is_active) {
            return false;
        }

        // Verificar si ha expirado
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si la IP está permitida
     */
    public function isIpAllowed(string $ip): bool
    {
        if (!$this->ip_whitelist) {
            return true; // Sin restricción de IP
        }

        $allowedIps = explode(',', $this->ip_whitelist);
        return in_array($ip, array_map('trim', $allowedIps));
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return true; // Sin restricción de permisos
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Marcar como usada (actualizar timestamp y contador)
     */
    public function markAsUsed(): void
    {
        $this->increment('requests_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Obtener key oculta (solo primeros 10 caracteres)
     */
    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 10) . '...' . substr($this->key, -4);
    }

    /**
     * Scope: Solo API Keys activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: API Keys no expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
