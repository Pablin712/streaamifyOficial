<?php

namespace App\Services;

use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConcentracionService
{
    /**
     * Returns related entity IDs from the employee's active tasks.
     * Used to filter views in concentration mode.
     *
     * @return array{iddet: int[], idcue: int[], idsop: int[]}
     */
    public function getIds(int $empId): array
    {
        $tareas = Tarea::where('assignee_id', $empId)
            ->where('completada', false)
            ->get(['tipo_tarea', 'related_id']);

        // iddet: cobrar_usuario / quitar_usuario tasks reference view_usuarios_activos.iddet
        $iddetList = $tareas->whereIn('tipo_tarea', ['cobrar_usuario', 'quitar_usuario'])
            ->pluck('related_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        // idcue directly from cuenta tasks — idcue is a string, never cast to int
        $idcueFromCuentaTasks = $tareas->whereIn('tipo_tarea', ['renovar_cuenta', 'cuenta_caida', 'colapso_cuenta'])
            ->pluck('related_id')
            ->filter()
            ->map(fn($v) => (string) $v)
            ->unique()
            ->values()
            ->toArray();

        // idcue via view_usuarios_activos for user tasks — keep as string
        $idcueFromUsers = !empty($iddetList)
            ? DB::table('view_usuarios_activos')
                ->whereIn('iddet', $iddetList)
                ->pluck('idcue')
                ->filter()
                ->map(fn($v) => (string) $v)
                ->unique()
                ->values()
                ->toArray()
            : [];

        // idsop: soporte_pendiente tasks reference soportes.idsop
        $idSopList = $tareas->where('tipo_tarea', 'soporte_pendiente')
            ->pluck('related_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        // idcue via soportes for soporte tasks — keep as string
        $idcueFromSoportes = !empty($idSopList)
            ? DB::table('soportes')
                ->whereIn('idsop', $idSopList)
                ->pluck('idcue')
                ->filter()
                ->map(fn($v) => (string) $v)
                ->unique()
                ->values()
                ->toArray()
            : [];

        $idcueList = array_values(array_unique(array_merge(
            $idcueFromCuentaTasks,
            $idcueFromUsers,
            $idcueFromSoportes
        )));

        return [
            'iddet' => $iddetList,
            'idcue' => $idcueList,
            'idsop' => $idSopList,
        ];
    }

    public static function isActive(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        return $user->hasRole('Trabajador externo') || (bool) session('modo_concentracion', false);
    }

    public static function isLocked(): bool
    {
        if (!Auth::check()) return false;
        /** @var \App\Models\Empleado $user */
        return Auth::user()->hasRole('Trabajador externo');
    }
}
