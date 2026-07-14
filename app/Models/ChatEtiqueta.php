<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatEtiqueta extends Model
{
    protected $table = 'chat_etiquetas';

    protected $fillable = [
        'nombre',
        'color',
    ];

    /**
     * Paleta fija de colores permitidos (estilo WhatsApp Business), para que las
     * etiquetas se vean consistentes con el resto de badges del panel.
     */
    public const PALETA = [
        '#ef4444', // rojo
        '#f97316', // naranja
        '#eab308', // amarillo
        '#22c55e', // verde
        '#06b6d4', // celeste
        '#3b82f6', // azul
        '#a855f7', // morado
        '#64748b', // gris
    ];

    public function conversaciones()
    {
        return $this->belongsToMany(
            Conversacion::class,
            'chat_conversacion_etiqueta',
            'etiqueta_id',
            'conversacion_id',
            'id',
            'idconv'
        );
    }
}
