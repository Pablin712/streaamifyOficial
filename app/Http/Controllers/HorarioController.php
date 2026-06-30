<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class HorarioController extends Controller
{
    public function store(Request $request)
    {
        if (!Gate::allows('cuentas')) {
            abort(403);
        }

        $validated = $request->validate([
            'empleado_id' => 'nullable|exists:empleados,idemp',
            'fecha'       => 'required|date|after_or_equal:today',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin'    => 'nullable|date_format:H:i',
            'notas'       => 'nullable|string|max:300',
        ]);

        $user = Auth::user();
        $empleadoId = ($user->hasAnyRole(['Admin', 'Gerente']) && !empty($validated['empleado_id']))
            ? (int) $validated['empleado_id']
            : $user->idemp;

        $horario = Horario::create([
            'empleado_id' => $empleadoId,
            'fecha'       => $validated['fecha'],
            'hora_inicio' => $validated['hora_inicio'] ?? null,
            'hora_fin'    => $validated['hora_fin'] ?? null,
            'notas'       => $validated['notas'] ?? null,
            'creado_por'  => $user->idemp,
            'cancelado'   => false,
        ]);

        return response()->json([
            'success'     => true,
            'id'          => $horario->id,
            'nombre'      => $user->nombreemp,
            'empleado_id' => $empleadoId,
            'es_mio'      => $empleadoId === $user->idemp,
            'estado'      => 'programado',
            'hora_inicio' => $horario->hora_inicio ? substr($horario->hora_inicio, 0, 5) : null,
            'hora_fin'    => $horario->hora_fin    ? substr($horario->hora_fin,    0, 5) : null,
            'notas'       => $horario->notas,
        ]);
    }

    public function destroy(int $id)
    {
        $horario = Horario::findOrFail($id);
        $user = Auth::user();

        if ($horario->empleado_id !== $user->idemp && !$user->hasAnyRole(['Admin', 'Gerente'])) {
            abort(403);
        }

        $horario->delete();

        return response()->json(['success' => true]);
    }
}
