<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FondoTransaccion extends Model
{
    protected $table = 'fondo_transacciones';

    protected $fillable = [
        'fondo_id', 'tipo', 'monto_anterior', 'monto_transaccion', 'monto_actualizado', 'referencia', 'fecha', 'anulada',
    ];

    protected $casts = [
        'monto_anterior' => 'decimal:4',
        'monto_transaccion' => 'decimal:4',
        'monto_actualizado' => 'decimal:4',
        'anulada' => 'boolean',
    ];

    public function fondo(): BelongsTo
    {
        return $this->belongsTo(Fondo::class, 'fondo_id');
    }
}
