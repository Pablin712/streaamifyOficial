<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Recarga extends Model
{
    use HasFactory;

    protected $table = 'recargas'; // Nombre de la tabla
    protected $primaryKey = 'idrec'; // Llave primaria

    protected $fillable = [
        'idcli',
        'numcomprobante',
        'valor',
        'foto',
        'comprobante_hash',
        'idestado',
        'idban',
        'origen',
        'external_reference',
        'metadata',
        'transaccion_id', // FK to transacciones
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relación con Cliente (una recarga pertenece a un cliente)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    // Relación con EstadoRecarga (una recarga tiene un estado)
    public function estado()
    {
        return $this->belongsTo(EstadoRecarga::class, 'idestado', 'idestado');
    }

    public function transaccion()
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id', 'id');
    }

    // Relación con Banco - mantener idban para el formulario de recarga
    // pero usar transaccion->banco cuando la recarga fue aprobada
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'idban', 'idban');
    }
}
