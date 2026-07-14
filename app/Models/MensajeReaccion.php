<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeReaccion extends Model
{
    protected $table = 'mensaje_reacciones';

    protected $fillable = [
        'idmsg',
        'autor_tipo',
        'emoji',
    ];

    public function mensaje()
    {
        return $this->belongsTo(Mensaje::class, 'idmsg', 'idmsg');
    }
}
