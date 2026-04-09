<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSubagente extends Model
{
    use HasFactory;

    protected $table = 'chat_subagentes';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'descripcion',
        'prompt_base',
        'criterios',
        'tools',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'criterios' => 'array',
        'tools' => 'array',
        'activo' => 'boolean',
    ];

    public function resumenes()
    {
        return $this->hasMany(ChatMemoriaResumen::class, 'subagente_id');
    }
}
