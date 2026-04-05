<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;
use App\Models\Cuenta;
use App\Models\Historial;
use App\Services\CuentaService;
use App\Services\EntregaMensajeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    protected $cuentaService;
    protected $entregaMensajeService;

    public function __construct(CuentaService $cuentaService, EntregaMensajeService $entregaMensajeService)
    {
        $this->cuentaService = $cuentaService;
        $this->entregaMensajeService = $entregaMensajeService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('usuarios')) {
            abort(403, 'No tienes permiso para ver los usuarios.');
        }

        if ($request->ajax() || $request->has('ajax') || $request->wantsJson()) {
            return $this->getUsuariosAjax($request);
        }

        $usuarios = $this->buildUsuariosIndexQuery()
            ->orderBy('vua.fecha_vencimiento')
            ->orderBy('vua.nombre_cliente')
            ->limit(10)
            ->get();

        $cuentas = Cuenta::where('activocue', true)->orderBy('idcue')->get();

        return view('inventory.usuarios.index', compact('usuarios', 'cuentas'));
    }

    private function getUsuariosAjax(Request $request)
    {
        $perPage = max(1, (int) $request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('search', ''));
        $sortBy = (string) $request->input('sort_by', '');
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = $this->buildUsuariosIndexQuery();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('vua.nombre_cliente', 'like', "%{$search}%")
                    ->orWhere('vua.idcue', 'like', "%{$search}%")
                    ->orWhere('vua.idven', 'like', "%{$search}%")
                    ->orWhere('vua.iddet', 'like', "%{$search}%")
                    ->orWhere('vua.perfil', 'like', "%{$search}%")
                    ->orWhere('vua.fecha_vencimiento', 'like', "%{$search}%")
                    ->orWhere('clientes.telefonocli', 'like', "%{$search}%")
                    ->orWhere('cuentas.usuariocue', 'like', "%{$search}%")
                    ->orWhere('detalles_venta.estado', 'like', "%{$search}%");
            });
        }

        $sortColumns = [
            '1' => 'vua.nombre_cliente',
            '2' => 'clientes.telefonocli',
            '3' => 'vua.idcue',
            '4' => 'cuentas.usuariocue',
            '5' => 'vua.perfil',
            '6' => 'vua.fecha_vencimiento',
            '7' => 'vua.fecha_vencimiento',
            '8' => 'detalles_venta.estado',
            'nombre_cliente' => 'vua.nombre_cliente',
            'telefonocli' => 'clientes.telefonocli',
            'idcue' => 'vua.idcue',
            'usuariocue' => 'cuentas.usuariocue',
            'perfil' => 'vua.perfil',
            'fecha_vencimiento' => 'vua.fecha_vencimiento',
            'estado' => 'vua.fecha_vencimiento',
            'cobro' => 'detalles_venta.estado',
        ];

        if (isset($sortColumns[$sortBy])) {
            $query->orderBy($sortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('vua.fecha_vencimiento')
                ->orderBy('vua.nombre_cliente');
        }

        $totalRecords = (clone $query)->count();
        $usuarios = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'html' => view('inventory.usuarios.partials.table-rows', compact('usuarios'))->render(),
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
        ]);
    }

    private function buildUsuariosIndexQuery()
    {
        return ViewUsuarioActivo::query()
            ->from('view_usuarios_activos as vua')
            ->select('vua.*')
            ->with(['cuenta.valor.servicio', 'cuenta.perfiles', 'cliente', 'profile', 'detalle_venta'])
            ->leftJoin('clientes', 'clientes.idcli', '=', 'vua.idcli')
            ->leftJoin('cuentas', 'cuentas.idcue', '=', 'vua.idcue')
            ->leftJoin('detalles_venta', 'detalles_venta.iddet', '=', 'vua.iddet');
    }

    // Método para mostrar el formulario de cambio de usuario
    public function change($iddet)
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar datos de usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();
        $cuentas = Cuenta::with('perfiles')->where('activocue', true)->orderBy('idcue')->get();

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        return view('inventory.usuarios.change', compact('usuario', 'cuentas'));
    }

    public function update(Request $request, $iddet)
    {
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'perfil' => 'required|integer|min:1',
            'fecha_vencimiento' => 'required'
        ]);

        $detalle = DetalleVenta::with(['venta.cliente', 'perfil.cuenta.valor.servicio'])->findOrFail($iddet);

        $servicioOrigen = optional(optional(optional($detalle->perfil)->cuenta)->valor->servicio)->nombreser ?? 'Servicio';

        // Actualizar los campos del usuario
        $detalle->idper = $request->idcue . '.' . $request->perfil;
        $detalle->fechavendet = $request->fecha_vencimiento;

        Historial::create([
            'accion' => 'Actualización de Usuario',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Datos antiguos: ' . json_encode($detalle),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $detalle->save();

        if ($request->expectsJson() || $request->ajax()) {
            $detalle->load(['venta.cliente', 'perfil.cuenta.valor.servicio']);

            $servicioDestino = optional(optional(optional($detalle->perfil)->cuenta)->valor->servicio)->nombreser ?? 'Servicio';
            $usuarioDestino = optional(optional($detalle->perfil)->cuenta)->usuariocue ?? '';
            $cuentaDestino = optional(optional($detalle->perfil)->cuenta)->idcue ?? '';
            $perfilDestino = optional($detalle->perfil)->numeroper ?? $request->perfil;
            $mensajeEntrega = $this->entregaMensajeService->mensajeEntregaCuenta(
                $detalle->perfil->cuenta,
                (int) $perfilDestino,
                optional($detalle->perfil)->pinper,
                $detalle->fechavendet,
                false,
                true
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente. Copia el mensaje para enviar al cliente.',
                'movements' => [[
                    'id_cliente' => optional($detalle->venta->cliente)->idcli,
                    'cliente' => optional($detalle->venta->cliente)->nombrecli ?? 'Cliente',
                    'telefono_cliente' => optional($detalle->venta->cliente)->telefonocli,
                    'telefono_normalizado' => optional($detalle->venta->cliente)->telefonocli
                        ? preg_replace('/\D+/', '', optional($detalle->venta->cliente)->telefonocli)
                        : null,
                    'servicio_origen' => $servicioOrigen,
                    'servicio_destino' => $servicioDestino,
                    'usuario_destino' => $usuarioDestino,
                    'cuenta_destino' => $cuentaDestino,
                    'perfil_destino' => $perfilDestino,
                    'mensaje_entrega' => $mensajeEntrega,
                ]],
            ]);
        }

        return redirect()->route('usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function enviarMensajeCliente(Request $request)
    {
        if (!Gate::allows('usuarios.update') && !Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para enviar mensajes a clientes.');
        }

        $validated = $request->validate([
            'cliente' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:50',
            'mensaje' => 'required|string|min:3|max:4000',
            'id_cliente' => 'nullable',
        ]);

        $telefonoNormalizado = preg_replace('/\D+/', '', $validated['telefono']);
        if (empty($telefonoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no tiene un teléfono válido para enviar el mensaje.',
            ], 422);
        }

        $webhookUrl = config('services.n8n.client_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'No está configurado el webhook de mensaje al cliente.',
            ], 500);
        }

        $empleado = Auth::user();
        $payload = [
            'event' => 'cliente.delivery_message',
            'trace_id' => 'cliente-msg-' . now()->format('Ymd-His') . '-' . substr($telefonoNormalizado, -6),
            'cliente' => [
                'idcli' => $validated['id_cliente'] ?? null,
                'nombre' => $validated['cliente'] ?? 'Cliente',
                'telefono' => $validated['telefono'],
                'telefono_normalizado' => $telefonoNormalizado,
            ],
            'mensaje' => trim($validated['mensaje']),
            'empleado' => [
                'idemp' => $empleado->idemp,
                'nombreemp' => $empleado->nombreemp,
                'usuarioemp' => $empleado->usuarioemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];

        try {
            $requestN8n = Http::acceptJson()
                ->timeout(10)
                ->retry(1, 300);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook n8n de mensaje individual al cliente devolvió error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'telefono' => $validated['telefono'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar el mensaje al webhook.',
                ], 502);
            }

            Historial::create([
                'accion' => 'Mensaje directo a cliente',
                'descripcion' => 'Cliente: ' . ($validated['cliente'] ?? 'Cliente') . ' | Teléfono: ' . $validated['telefono'],
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente al cliente por n8n.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando mensaje individual a cliente vía n8n', [
                'error' => $e->getMessage(),
                'telefono' => $validated['telefono'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el mensaje al cliente.',
            ], 500);
        }
    }

    public function moverUsuario($iddet){
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        $respuesta = $this->cuentaService->mudarClienteAOtraCuenta($usuario);
        if (($respuesta['status'] ?? null) === 'error') {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo mover el usuario, probablemente ya no quedan espacios',
                ], 422);
            }

            return redirect()->back()->with('error', 'No se pudo mover el usuario, probablemente ya no quedan espacios');
        }

        $movement = $respuesta['movement'] ?? null;

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario movido correctamente.',
                'movements' => $movement ? [$movement] : [],
            ]);
        }

        return redirect()->back()->with('success', 'Usuario movido correctamente.');
    }
    public function moverUsuarioMesa($iddet){
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        $respuesta = $this->cuentaService->mudarClienteAMesaDeTrabajo($usuario);
        if($respuesta == 'error'){
            return redirect()->back()->with('error', 'No se pudo mover el usuario, probablemente ya no quedan espacios');
        }
        else{
            return redirect()->back()->with('success', $respuesta);
        }
    }
    public function actualizarEstadoCobro(Request $request, $iddet)
    {
        try {
            $request->validate([
                'estado' => 'required|in:COBRADO,PENDIENTE'
            ]);

            $detalle = DetalleVenta::findOrFail($iddet);
            $estadoAnterior = $detalle->estado;
            $detalle->estado = $request->estado;
            $detalle->save();

            Historial::create([
                'accion' => 'Actualización Estado de Cobro',
                'descripcion' => 'Usuario ID: ' . $iddet . ' - Estado cambió de ' . $estadoAnterior . ' a ' . $request->estado,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estado' => $detalle->estado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($iddet)
    {
        if (!Gate::allows('usuarios.destroy')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        $detalle = DetalleVenta::findOrFail($iddet);
        // Invertir el estado de activodet
        $detalle->activodet = !$detalle->activodet;
        $detalle->save();

        Historial::create([
            'accion' => 'Cuenta-Quitada',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Usuario que se quitó: ' . json_encode($detalle),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Usuario eliminado con éxito.');
    }
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('usuarios', []);
        $ids = array_filter($ids, 'is_numeric');
        if (!Gate::allows('usuarios.destroy')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        if (!empty($ids)) {
            $detalles = DetalleVenta::whereIn('iddet', $ids)->get();
            foreach ($detalles as $detalle) {
                $detalle->activodet = !$detalle->activodet;
                $detalle->save();

                Historial::create([
                    'accion' => 'Cuenta-Quitada',
                    'descripcion' => 'Cliente: ' . ($detalle->venta->cliente->nombrecli ?? 'N/A') . ' - Usuario que se quitó: ' . json_encode($detalle),
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Usuarios eliminados correctamente.');
    }

    /**
     * Mover usuario a un servicio diferente
     */
    public function moverUsuarioOtroServicio(Request $request, $iddet)
    {
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }

        $request->validate([
            'idser_destino' => 'required|string'
        ]);

        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        if (!$usuario) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        $idserDestino = $request->idser_destino;

        $respuesta = $this->cuentaService->mudarClienteAOtroServicio($usuario, $idserDestino);

        if (($respuesta['status'] ?? null) === 'error_no_disponible') {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cuentas disponibles en el servicio ' . $idserDestino,
                ], 422);
            }

            return redirect()->back()->with('error', 'No hay cuentas disponibles en el servicio ' . $idserDestino);
        }

        if (($respuesta['status'] ?? null) === 'error_sin_perfil') {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay perfiles disponibles en las cuentas del servicio ' . $idserDestino,
                ], 422);
            }

            return redirect()->back()->with('error', 'No hay perfiles disponibles en las cuentas del servicio ' . $idserDestino);
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario movido a otro servicio correctamente.',
                'movements' => isset($respuesta['movement']) ? [$respuesta['movement']] : [],
            ]);
        }

        return redirect()->back()->with('success', 'Usuario movido a otro servicio correctamente.');
    }

    public function marcarCuentaDanada($iddet)
    {
        if (!Gate::allows('usuarios.update') && !Gate::allows('cuentas.status')) {
            abort(403, 'No tienes permiso para marcar cuentas como dañadas.');
        }

        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        $cuenta = Cuenta::with('valor')->find($usuario->idcue);

        if (!$cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Cuenta no encontrada.'
            ], 404);
        }

        if ($cuenta->caidacue) {
            return response()->json([
                'success' => true,
                'message' => 'La cuenta ya estaba marcada como dañada.',
                'cuenta' => $cuenta->idcue
            ]);
        }

        $cuenta->caidacue = true;
        $cuenta->save();

        Historial::create([
            'accion' => 'Cuenta Marcada como Dañada desde Usuarios',
            'descripcion' => 'Cuenta: ' . $cuenta->idcue . ' | Usuario afectado IDDET: ' . $iddet,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        if ($cuenta->valor && $cuenta->valor->idser) {
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cuenta marcada como dañada correctamente.',
            'cuenta' => $cuenta->idcue
        ]);
    }
}
