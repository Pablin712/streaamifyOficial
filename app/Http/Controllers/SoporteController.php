<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Soporte;
use App\Services\ConcentracionService;
use App\Models\ViewUsuarioActivo;
use App\Notifications\NuevoSoporteCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class SoporteController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('soportes')) {
            abort(403, 'No tienes permiso para ver los soportes.');
        }

        $soportesQuery = Soporte::with(['cliente', 'cuenta.valor.servicio'])
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if (session('modo_concentracion')) {
            $concIds = app(ConcentracionService::class)->getIds($user->idemp)['idsop'];
            if (!empty($concIds)) {
                $soportesQuery->whereIn('idsop', $concIds);
            } else {
                $soportesQuery->whereRaw('1 = 0');
            }
        }

        $soportes = $soportesQuery->get();

        $usuariosActivos = ViewUsuarioActivo::with(['profile'])
            ->whereIn('idcue', $soportes->pluck('idcue')->filter()->unique()->values())
            ->whereIn('idcli', $soportes->pluck('idcli')->filter()->unique()->values())
            ->orderByDesc('fecha_vencimiento')
            ->get()
            ->unique(fn ($usuario) => $usuario->idcue . '|' . $usuario->idcli)
            ->keyBy(fn ($usuario) => $usuario->idcue . '|' . $usuario->idcli);

        $soportes->each(function (Soporte $soporte) use ($usuariosActivos) {
            $soporte->setRelation(
                'usuarioActivoSoporte',
                $usuariosActivos->get($soporte->idcue . '|' . $soporte->idcli)
            );
        });

        return view('inventory.soportes.index', compact('soportes'));
    }

    public function storeCliente(Request $request)
    {
        /** @var \App\Models\Cliente $cliente */
        $cliente = Auth::guard('cliente')->user();

        $cuentasCliente = ViewUsuarioActivo::where('idcli', $cliente->idcli)
            ->pluck('idcue')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validated = $request->validate([
            'idcue' => ['required', Rule::in($cuentasCliente)],
            'tipo' => ['required', Rule::in(Soporte::TIPOS)],
            'descripcion' => ['required', 'string', 'min:10', 'max:1500'],
        ]);

        $soporte = Soporte::create([
            'idcli' => $cliente->idcli,
            'idcue' => $validated['idcue'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'estado' => 'pendiente',
        ]);

        $soporte->load(['cliente', 'cuenta.valor.servicio']);

        $empleados = Empleado::permission('soportes')->get();
        if ($empleados->isNotEmpty()) {
            Notification::send($empleados, new NuevoSoporteCliente($soporte));
        }

        event('notificacionRecibida');

        return redirect()
            ->route('historial.cliente')
            ->with('success', 'Tu soporte fue enviado correctamente. Un técnico lo revisará pronto.')
            ->with('active_tab', '#soportes');
    }

    public function atender(Request $request, $idsop)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('soportes.update')) {
            abort(403, 'No tienes permiso para atender soportes.');
        }

        $validated = $request->validate([
            'solucion' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $soporte = Soporte::with(['cliente', 'cuenta'])->findOrFail($idsop);

        $soporte->update([
            'solucion' => $validated['solucion'],
            'estado' => 'atendido',
        ]);

        Historial::create([
            'accion' => 'Atención de soporte',
            'descripcion' => 'Soporte #' . $soporte->idsop . ' atendido para cliente ' . ($soporte->cliente?->nombrecli ?? 'N/A') . ' en cuenta ' . $soporte->idcue,
            'empleado_id' => $user->idemp,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soporte atendido correctamente.',
            'soporte' => $soporte->fresh(['cliente', 'cuenta.valor.servicio']),
        ]);
    }
}
