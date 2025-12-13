<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deuda extends Model
{
    protected $fillable = [
        'proveedor_id', 'monto', 'monto_pagado', 'estado'
    ];

    // Relación con proveedor
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idpro');
    }

    // Obtener monto restante de la deuda
    public function getMontoRestanteAttribute()
    {
        return $this->monto - $this->monto_pagado;
    }
}
