<?php

namespace App\Services;

use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConcentracionService
{
    /**
     * Returns all filtered entity IDs based on the employee's active tasks.
     * Rules are strict per task type (RequisitosV7.2):
     *
     *   soporte_pendiente  → soportes, chats(clientes), cuentas, usuarios
     *   renovar_cuenta     → chats(proveedores), cuentas, usuarios
     *   quitar_usuario     → chats(clientes), cuentas, usuarios
     *   cobrar_usuario     → chats(clientes), usuarios              (NO cuentas)
     *   cuenta_caida       → soportes(related), chats(clientes), cuentas, usuarios
     *   colapso_cuenta     → chats(proveedores), cuentas, usuarios
     *   agregar_stock      → chats(todos proveedores), cuentas(solo vista), servicios, valores
     *
     * @return array{
     *   iddet: int[],
     *   idcue: string[],
     *   idsop: int[],
     *   idcli: int[],
     *   idcue_providers: string[],
     *   all_providers: bool
     * }
     */
    public function getIds(int $empId): array
    {
        $tareas = Tarea::where('assignee_id', $empId)
            ->where('completada', false)
            ->get(['tipo_tarea', 'related_id']);

        $hasAgregarStock = $tareas->where('tipo_tarea', 'agregar_stock')->isNotEmpty();

        // ─── Tareas generales (rol temporal, RequisitosV7.4) ─────────────────
        $hasVender        = $tareas->where('tipo_tarea', 'general_vender')->isNotEmpty();
        $hasAtender       = $tareas->where('tipo_tarea', 'general_atender_clientes')->isNotEmpty();
        $hasAdminCuentas  = $tareas->where('tipo_tarea', 'general_administrar_cuentas')->isNotEmpty();

        // ─── iddet: direct user tasks (cobrar + quitar) ──────────────────────
        $iddetCobrar = $tareas->where('tipo_tarea', 'cobrar_usuario')
            ->pluck('related_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray();

        $iddetQuitar = $tareas->where('tipo_tarea', 'quitar_usuario')
            ->pluck('related_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray();

        $iddetDirect = array_values(array_unique(array_merge($iddetCobrar, $iddetQuitar)));

        // ─── idcue from account tasks ────────────────────────────────────────
        $idcueCaida = $tareas->where('tipo_tarea', 'cuenta_caida')
            ->pluck('related_id')->filter()->map(fn($v) => (string) $v)->unique()->values()->toArray();

        $idcueRenovar = $tareas->where('tipo_tarea', 'renovar_cuenta')
            ->pluck('related_id')->filter()->map(fn($v) => (string) $v)->unique()->values()->toArray();

        $idcueColapso = $tareas->where('tipo_tarea', 'colapso_cuenta')
            ->pluck('related_id')->filter()->map(fn($v) => (string) $v)->unique()->values()->toArray();

        $idcueAllAccountTasks = array_values(array_unique(array_merge(
            $idcueCaida, $idcueRenovar, $idcueColapso
        )));

        // ─── idsop: soporte_pendiente tasks ──────────────────────────────────
        $idSopDirect = $tareas->where('tipo_tarea', 'soporte_pendiente')
            ->pluck('related_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray();

        // ─── idcue derived from soporte tasks ────────────────────────────────
        $idcueFromSoportes = ! empty($idSopDirect)
            ? DB::table('soportes')->whereIn('idsop', $idSopDirect)
                ->pluck('idcue')->filter()->map(fn($v) => (string) $v)->unique()->values()->toArray()
            : [];

        // ─── idcue from quitar_usuario (only quitar grants cuentas access) ───
        $idcueFromQuitar = ! empty($iddetQuitar)
            ? DB::table('view_usuarios_activos')->whereIn('iddet', $iddetQuitar)
                ->pluck('idcue')->filter()->map(fn($v) => (string) $v)->unique()->values()->toArray()
            : [];

        // ─── idcue for CUENTAS view (quitar + accounts + soportes; not cobrar) ─
        $idcueForCuentas = array_values(array_unique(array_merge(
            $idcueAllAccountTasks, $idcueFromSoportes, $idcueFromQuitar
        )));

        // ─── iddet from account+soporte tasks (users in those accounts) ──────
        $allIdcueForUsers = array_values(array_unique(array_merge(
            $idcueAllAccountTasks, $idcueFromSoportes
        )));
        $iddetFromAccounts = ! empty($allIdcueForUsers)
            ? DB::table('view_usuarios_activos')->whereIn('idcue', $allIdcueForUsers)
                ->pluck('iddet')->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray()
            : [];

        // ─── All iddet for USUARIOS view ─────────────────────────────────────
        $allIddet = array_values(array_unique(array_merge($iddetDirect, $iddetFromAccounts)));

        // ─── idsop from cuenta_caida accounts (for SOPORTES view) ────────────
        $idSopFromCaida = ! empty($idcueCaida)
            ? DB::table('soportes')
                ->whereIn('idcue', $idcueCaida)
                ->where('estado', 'pendiente')
                ->pluck('idsop')->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray()
            : [];

        $allIdSop = array_values(array_unique(array_merge($idSopDirect, $idSopFromCaida)));

        // ─── idcli for CLIENT chats ───────────────────────────────────────────
        // cobrar + quitar → clients via view_usuarios_activos
        $clientsFromUsers = ! empty($iddetDirect)
            ? DB::table('view_usuarios_activos')->whereIn('iddet', $iddetDirect)
                ->pluck('idcli')->filter()->unique()->map(fn($v) => (int) $v)->toArray()
            : [];
        // soporte → clients via soportes
        $clientsFromSoportes = ! empty($idSopDirect)
            ? DB::table('soportes')->whereIn('idsop', $idSopDirect)
                ->pluck('idcli')->filter()->unique()->map(fn($v) => (int) $v)->toArray()
            : [];
        // cuenta_caida → clients via view_usuarios_activos
        $clientsFromCaida = ! empty($idcueCaida)
            ? DB::table('view_usuarios_activos')->whereIn('idcue', $idcueCaida)
                ->pluck('idcli')->filter()->unique()->map(fn($v) => (int) $v)->toArray()
            : [];

        $allowedClientIds = array_values(array_unique(array_merge(
            $clientsFromUsers, $clientsFromSoportes, $clientsFromCaida
        )));

        // ─── idcue for PROVIDER chats (renovar + colapso only) ───────────────
        $idcueForProviders = array_values(array_unique(array_merge($idcueRenovar, $idcueColapso)));

        return [
            'iddet'           => $allIddet,
            'idcue'           => $idcueForCuentas,
            'idsop'           => $allIdSop,
            'idcli'           => $allowedClientIds,
            'idcue_providers' => $idcueForProviders,
            // agregar_stock y el rol temporal "administrar cuentas" necesitan hablar con todos los proveedores
            'all_providers'   => $hasAgregarStock || $hasAdminCuentas,
            // Tareas generales: desbloquean el alcance completo de un dominio sin filtrar por IDs
            'all_cuentas'     => $hasAtender || $hasAdminCuentas,
            'all_usuarios'    => $hasAtender,
            'all_soportes'    => $hasAtender,
            'all_clientes'    => $hasVender || $hasAtender,
        ];
    }

    /**
     * Indica si el empleado tiene, mediante una tarea general activa (RequisitosV7.4),
     * un permiso de escritura adicional que su rol no otorga por sí solo.
     */
    public static function abilityGrantedByGeneralTask(int $empId, string $ability): bool
    {
        $tiposQueOtorgan = array_keys(array_filter(
            Tarea::GENERAL_PERMISSIONS,
            fn($permisos) => in_array($ability, $permisos, true)
        ));

        if (empty($tiposQueOtorgan)) {
            return false;
        }

        static $activosPorEmpleado = [];

        if (! array_key_exists($empId, $activosPorEmpleado)) {
            $activosPorEmpleado[$empId] = Tarea::where('assignee_id', $empId)
                ->where('completada', false)
                ->whereIn('tipo_tarea', Tarea::GENERAL_TYPES)
                ->pluck('tipo_tarea')
                ->all();
        }

        return ! empty(array_intersect($tiposQueOtorgan, $activosPorEmpleado[$empId]));
    }

    public static function isActive(): bool
    {
        if (! Auth::check()) {
            return false;
        }
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        return $user->hasRole('Trabajador externo') || (bool) session('modo_concentracion', false);
    }

    public static function isLocked(): bool
    {
        if (! Auth::check()) return false;
        /** @var \App\Models\Empleado $user */
        return Auth::user()->hasRole('Trabajador externo');
    }
}
