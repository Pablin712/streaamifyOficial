<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    use HasFactory;

    protected $table = 'metas';
    protected $primaryKey = 'idmet';

    protected $fillable = [
        'kpi',
        'objetivo',
        'periodo',
        'anio',
        'mes',
        'umbral_atencion',
        'activo',
        'nota',
    ];

    protected $casts = [
        'objetivo'        => 'float',
        'anio'            => 'integer',
        'mes'             => 'integer',
        'umbral_atencion' => 'integer',
        'activo'          => 'boolean',
    ];

    /**
     * Metas que aplican a un periodo: las fijadas para ese mes/anio concreto
     * y las permanentes (anio y mes en NULL), que se repiten cada periodo.
     */
    public function scopeVigentes($query, int $mes, int $anio)
    {
        return $query->where('activo', true)
            ->where(function ($q) use ($mes, $anio) {
                $q->where(function ($p) use ($mes, $anio) {
                    $p->where('anio', $anio)->where('mes', $mes);
                })
                ->orWhere(function ($p) use ($anio) {
                    $p->where('anio', $anio)->whereNull('mes');
                })
                ->orWhere(function ($p) {
                    $p->whereNull('anio')->whereNull('mes');
                });
            });
    }
}
