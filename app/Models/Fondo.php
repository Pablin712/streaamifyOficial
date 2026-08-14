<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fondo extends Model
{
    protected $table = 'fondos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'saldo',
        'activo',
    ];

    protected $casts = [
        'saldo' => 'decimal:4',
        'activo' => 'boolean',
    ];

    public function transacciones(): HasMany
    {
        return $this->hasMany(FondoTransaccion::class, 'fondo_id');
    }
}
