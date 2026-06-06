<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombretarea',
        'descripcion',
        'prioridad',
        'completada',
        'fechalimit',
        'completada_por',
        'fecha_completada',
        'assignee_id',
        'asignado_por',
        'assigned_at',
        'tipo_tarea',
        'related_model',
        'related_id',
    ];

    protected $casts = [
        'completada'       => 'boolean',
        'fechalimit'       => 'datetime',
        'fecha_completada' => 'datetime',
        'assigned_at'      => 'datetime',
    ];

    // Tipos de tarea generados automáticamente
    const TIPOS = [
        'cobrar_usuario'   => ['label' => 'Cobrar usuario',     'icon' => '💰', 'color' => 'warning'],
        'quitar_usuario'   => ['label' => 'Quitar usuario',     'icon' => '✂️',  'color' => 'danger'],
        'renovar_cuenta'   => ['label' => 'Renovar cuenta',     'icon' => '🔄', 'color' => 'primary'],
        'cuenta_caida'     => ['label' => 'Cuenta caída',       'icon' => '⚠️',  'color' => 'danger'],
        'colapso_cuenta'   => ['label' => 'Ajustar espacios',   'icon' => '📌', 'color' => 'secondary'],
        'soporte_pendiente'=> ['label' => 'Soporte pendiente',  'icon' => '🎧', 'color' => 'info'],
        'agregar_stock'    => ['label' => 'Agregar stock',      'icon' => '📦', 'color' => 'success'],
        'manual'           => ['label' => 'Tarea manual',       'icon' => '📝', 'color' => 'secondary'],
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function assignee()
    {
        return $this->belongsTo(Empleado::class, 'assignee_id', 'idemp');
    }

    public function asignadoPorEmp()
    {
        return $this->belongsTo(Empleado::class, 'asignado_por', 'idemp');
    }

    public function completadaPorEmp()
    {
        return $this->belongsTo(Empleado::class, 'completada_por', 'idemp');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->whereNull('assignee_id')->where('completada', false);
    }

    public function scopeAsignadasA($query, int $empId)
    {
        return $query->where('assignee_id', $empId)->where('completada', false);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo_tarea]['label'] ?? $this->nombretarea;
    }

    public function tipoIcon(): string
    {
        return self::TIPOS[$this->tipo_tarea]['icon'] ?? '📝';
    }

    public function tipoColor(): string
    {
        return self::TIPOS[$this->tipo_tarea]['color'] ?? 'secondary';
    }
}
