<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deudor extends Model
{
    protected $table = 'deudores';

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class, 'deudor_id');
    }

    public function getMontoPrestadoPendienteAttribute()
    {
        return $this->prestamos()->where('estado', 'pendiente')->get()->sum('monto_restante');
    }
}
