<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    protected $table = 'mails'; // Nombre de la tabla (opcional si sigue convención)

    protected $fillable = [
        'email',
        'password',
        'host',
        'description',
        // agrega aquí otros campos que tenga tu tabla
    ];

    public $timestamps = true; // o false si tu tabla no tiene created_at/updated_at
}
