<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codigo extends Model
{
    use HasFactory;

    protected $table = 'codigos';

    protected $fillable = [
        'codigo',
        'mensaje',
        'telefono',
        'idcli',
        'idcue',
        'usuariocue',
        'idser',
        'instance',
        'apikey',
        'usuarios_habilitados',
        'estado',
    ];

    const UPDATED_AT = null;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
