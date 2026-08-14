<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestamo extends Model
{
    protected $table = 'prestamos';

    protected $fillable = [
        'deudor_id',
        'monto',
        'monto_pagado',
        'estado',
        'banco_id',
        'banco_transaccion_id',
        'fondo_id',
        'fondo_transaccion_id',
        'fecha',
        'motivo',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function deudor(): BelongsTo
    {
        return $this->belongsTo(Deudor::class, 'deudor_id');
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id', 'idban');
    }

    public function fondo(): BelongsTo
    {
        return $this->belongsTo(Fondo::class, 'fondo_id');
    }

    public function bancoTransaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'banco_transaccion_id');
    }

    public function fondoTransaccion(): BelongsTo
    {
        return $this->belongsTo(FondoTransaccion::class, 'fondo_transaccion_id');
    }

    public function getMontoRestanteAttribute()
    {
        return $this->monto - $this->monto_pagado;
    }

    public function getOrigenNombreAttribute(): string
    {
        return $this->banco_id ? ($this->banco->nombreban ?? 'Banco') : ($this->fondo->nombre ?? 'Fondo');
    }
}
