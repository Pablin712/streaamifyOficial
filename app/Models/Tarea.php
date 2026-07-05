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

        // Tareas generales: rol temporal, solo asignable por Admin/Gerente (RequisitosV7.4)
        'general_vender'              => ['label' => 'Rol temporal: Vender',              'icon' => '🛒', 'color' => 'primary'],
        'general_atender_clientes'    => ['label' => 'Rol temporal: Atender clientes',    'icon' => '🎧', 'color' => 'info'],
        'general_administrar_cuentas' => ['label' => 'Rol temporal: Administrar cuentas', 'icon' => '🗄️', 'color' => 'success'],
    ];

    // Tipos de tarea "general": dan acceso amplio y sin filtrar a un dominio mientras estén activas
    const GENERAL_TYPES = [
        'general_vender',
        'general_atender_clientes',
        'general_administrar_cuentas',
    ];

    // Permisos de escritura adicionales que otorga cada tarea general (ver ConcentracionService::abilityGrantedByGeneralTask)
    const GENERAL_PERMISSIONS = [
        'general_vender' => [
            'ventas.create', 'ventas.store', 'ventas.storeRenew', 'ventas.storeCliente',
            'ventas.status', 'ventas.edit', 'ventas.renew', 'ventas.update', 'ventas.sendInvoice',
            'clientes.create', 'clientes.store', 'clientes.edit', 'clientes.update',
        ],
        'general_atender_clientes' => [],
        'general_administrar_cuentas' => [
            'servicios.create', 'servicios.store', 'servicios.edit', 'servicios.update',
            'valores.edit', 'valores.update',
        ],
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
