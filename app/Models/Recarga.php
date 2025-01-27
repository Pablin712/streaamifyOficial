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
        'idestado',
        'idban',
    ];

    // Relación con Cliente (una recarga pertenece a un cliente)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'id');
    }

    // Relación con EstadoRecarga (una recarga tiene un estado)
    public function estado()
    {
        return $this->belongsTo(EstadoRecarga::class, 'idestado', 'idestado');
    }

    // Relación con Banco (una recarga pertenece a un banco)
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'idban', 'idban');
    }
}
