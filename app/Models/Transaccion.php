<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaccion extends Model
{
    protected $table = 'transacciones';

    protected $fillable = [
        'banco_id', 'tipo', 'monto_anterior', 'monto_transaccion', 'monto_actualizado', 'referencia', 'fecha', 'anulada'
    ];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id', 'idban');
    }
}
