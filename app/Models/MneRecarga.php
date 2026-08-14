<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MneRecarga extends Model
{
    protected $table = 'mne_recargas';

    protected $fillable = [
        'operadora',
        'cliente_nombre',
        'cliente_telefono',
        'valor_cobrado',
        'costo_fondo',
        'ganancia',
        'fondo_id',
        'fondo_transaccion_id',
        'banco_id',
        'banco_transaccion_id',
        'fondo_cobro_id',
        'fondo_cobro_transaccion_id',
        'fecha',
        'notas',
        'anulada',
    ];

    protected $casts = [
        'valor_cobrado' => 'decimal:2',
        'costo_fondo' => 'decimal:4',
        'ganancia' => 'decimal:4',
        'fecha' => 'datetime',
        'anulada' => 'boolean',
    ];

    // Fondo operativo consumido (ej. "Mi Negocio Efectivo")
    public function fondo(): BelongsTo
    {
        return $this->belongsTo(Fondo::class, 'fondo_id');
    }

    public function fondoTransaccion(): BelongsTo
    {
        return $this->belongsTo(FondoTransaccion::class, 'fondo_transaccion_id');
    }

    // Donde entro el cobro al cliente (banco real, si aplica)
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id', 'idban');
    }

    public function bancoTransaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'banco_transaccion_id');
    }

    // Donde entro el cobro al cliente (fondo "Efectivo", si aplica)
    public function fondoCobro(): BelongsTo
    {
        return $this->belongsTo(Fondo::class, 'fondo_cobro_id');
    }

    public function fondoCobroTransaccion(): BelongsTo
    {
        return $this->belongsTo(FondoTransaccion::class, 'fondo_cobro_transaccion_id');
    }

    public function getCobroNombreAttribute(): ?string
    {
        if ($this->banco_id) {
            return $this->banco->nombreban ?? 'Banco';
        }
        if ($this->fondo_cobro_id) {
            return $this->fondoCobro->nombre ?? 'Efectivo';
        }
        return null;
    }
}
