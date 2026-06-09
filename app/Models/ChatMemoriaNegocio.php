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
        'categoria',
        'clave',
        'titulo',
        'contenido',
        'resumen',
        'visibilidad',
        'tags',
        'fuente',
        'prioridad',
        'activo',
        'inicio_at',
        'fin_at',
    ];

    public const TIPOS = [
        'faq'                 => 'FAQ',
        'servicio'            => 'Servicio',
        'metodo_pago'         => 'Método de pago',
        'politica_venta'      => 'Política de venta',
        'politica_descuento'  => 'Política de descuento',
        'confianza'           => 'Confianza',
        'marca'               => 'Marca',
        'objecion'            => 'Objeción',
        'guion'               => 'Guion',
        'campaña'             => 'Campaña',
        'soporte_pasos'       => 'Pasos soporte',
        'soporte_escalado'    => 'Criterio escalado',
    ];

    public const CATEGORIAS = [
        'general'        => '🌐 General',
        'soporte'        => '🔧 Soporte General',
        'netflix'        => '🔴 Netflix',
        'disney_plus'    => '🔵 Disney+',
        'max'            => '🟣 Max (HBO)',
        'paramount_plus' => '⭐ Paramount+',
        'crunchyroll'    => '🟠 Crunchyroll',
        'flujo_tv'       => '📺 Flujo TV',
        'spotify'        => '🎵 Spotify',
        'prime_video'    => '🔷 Prime Video',
    ];

    protected $casts = [
        'tags'      => 'array',
        'activo'    => 'boolean',
        'inicio_at' => 'datetime',
        'fin_at'    => 'datetime',
    ];

    public function estaVigente(): bool
    {
        if (! $this->activo) return false;
        $now = now();
        if ($this->inicio_at && $this->inicio_at->gt($now)) return false;
        if ($this->fin_at && $this->fin_at->lt($now)) return false;
        return true;
    }

    public function estadoVigencia(): string
    {
        if (! $this->activo) return 'inactiva';
        $now = now();
        if ($this->inicio_at && $this->inicio_at->gt($now)) return 'programada';
        if ($this->fin_at && $this->fin_at->lt($now)) return 'expirada';
        return 'activa';
    }
}
