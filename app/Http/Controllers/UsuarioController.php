<?php

namespace App\Http\Controllers;

use App\Models\ChatContactoCanal;
use App\Models\ChatMensajeCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Conversacion;
use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;
use App\Models\Cuenta;
use App\Models\Historial;
use App\Models\Mensaje;
use App\Models\Proveedor;
use App\Services\ConcentracionService;
use App\Services\CuentaService;
use App\Services\EntregaMensajeService;
use App\Services\Chat\WhatsAppOutboundService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    protected $cuentaService;
    protected $entregaMensajeService;
    protected $whatsAppOutboundService;

    public function __construct(
        CuentaService $cuentaService,
        EntregaMensajeService $entregaMensajeService,
        WhatsAppOutboundService $whatsAppOutboundService
    )
    {
        $this->cuentaService = $cuentaService;
        $this->entregaMensajeService = $entregaMensajeService;
        $this->whatsAppOutboundService = $whatsAppOutboundService;
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
        $proveedores = Proveedor::where('activopro', true)->orderBy('nombrepro')->get();
        // Estadísticas globales: información sensible, oculta para trabajadores externos
        $stats = ConcentracionService::isLocked() ? null : $this->calcularStatsUsuarios(Carbon::today());

        return view('inventory.usuarios.index', compact('usuarios', 'cuentas', 'proveedores', 'stats'));
    }

    private function calcularStatsUsuarios(Carbon $today): array
    {
        $totalUsuarios = DB::table('view_usuarios_activos')->count();

        $cuentaDanada = DB::table('view_usuarios_activos as vua')
            ->join('cuentas as c', 'c.idcue', '=', 'vua.idcue')
            ->where('c.caidacue', true)
            ->where('c.idcue', 'not like', '%-Atencion')
            ->count();

        $mesaTrabajo = DB::table('view_usuarios_activos as vua')
            ->join('cuentas as c', 'c.idcue', '=', 'vua.idcue')
            ->where('c.idcue', 'like', '%-Atencion')
            ->count();

        $aCobrarHoy = DB::table('view_usuarios_activos')
            ->whereDate('fecha_vencimiento', $today)
            ->count();

        $atrasados = DB::table('view_usuarios_activos')
            ->whereDate('fecha_vencimiento', '<', $today)
            ->count();

        $proximosVencer = DB::table('view_usuarios_activos')
            ->whereDate('fecha_vencimiento', '>', $today)
            ->whereDate('fecha_vencimiento', '<=', $today->copy()->addDays(7))
            ->count();

        $porServicio = DB::table('view_usuarios_activos as vua')
            ->join('cuentas as cv', 'cv.idcue', '=', 'vua.idcue')
            ->join('valores as vl', 'vl.idval', '=', 'cv.idval')
            ->join('servicios as s', 's.idser', '=', 'vl.idser')
            ->select('s.idser', 's.nombreser', DB::raw('COUNT(*) as total'))
            ->groupBy('s.idser', 's.nombreser')
            ->orderByDesc('total')
            ->get();

        $servicioTop = $porServicio->first();

        $totalClientes = DB::table('view_usuarios_activos')
            ->distinct('idcli')
            ->count('idcli');

        $promedioCliente = $totalClientes > 0
            ? round($totalUsuarios / $totalClientes, 1)
            : 0;

        return [
            'total'             => $totalUsuarios,
            'cuenta_danada'     => $cuentaDanada,
            'mesa_trabajo'      => $mesaTrabajo,
            'requieren_soporte' => $cuentaDanada + $mesaTrabajo,
            'a_cobrar_hoy'      => $aCobrarHoy,
            'atrasados'         => $atrasados,
            'proximos_vencer'   => $proximosVencer,
            'por_servicio'      => $porServicio,
            'servicio_top'      => $servicioTop,
            'total_clientes'    => $totalClientes,
            'promedio_cliente'  => $promedioCliente,
        ];
    }

    private function getUsuariosAjax(Request $request)
    {
        $perPage = max(1, (int) $request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('search', ''));
        $sortBy = (string) $request->input('sort_by', '');
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $servicio = strtoupper(trim((string) $request->input('servicio', '')));
        $proveedor = (int) $request->input('proveedor', 0);

        $query = $this->buildUsuariosIndexQuery();

        // Filtro por servicio o proveedor (join a valores una sola vez)
        if (($servicio && $servicio !== 'TODOS') || $proveedor > 0) {
            $query->join('valores as vf', 'vf.idval', '=', 'cuentas.idval');
            if ($servicio && $servicio !== 'TODOS') {
                $query->where('vf.idser', $servicio);
            }
            if ($proveedor > 0) {
                $query->where('vf.idpro', $proveedor);
            }
        }

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
        $query = ViewUsuarioActivo::query()
            ->from('view_usuarios_activos as vua')
            ->select('vua.*')
            ->with(['cuenta.valor.servicio', 'cuenta.perfiles', 'cliente', 'profile', 'detalle_venta'])
            ->leftJoin('clientes', 'clientes.idcli', '=', 'vua.idcli')
            ->leftJoin('cuentas', 'cuentas.idcue', '=', 'vua.idcue')
            ->leftJoin('detalles_venta', 'detalles_venta.iddet', '=', 'vua.iddet');

        if (ConcentracionService::isActive()) {
            $concIdsAll = app(ConcentracionService::class)->getIds(Auth::user()->idemp);
            if (! ($concIdsAll['all_usuarios'] ?? false)) {
                $concIds = $concIdsAll['iddet'];
                if (!empty($concIds)) {
                    $query->whereIn('vua.iddet', $concIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        return $query;
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
            'channel_preference' => 'nullable|in:verde,azul,alterno',
        ]);

        $telefonoNormalizado = preg_replace('/\D+/', '', $validated['telefono']);
        if (empty($telefonoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no tiene un teléfono válido para enviar el mensaje.',
            ], 422);
        }

        $channelPreference = $validated['channel_preference'] ?? 'verde';
        if ($channelPreference === 'azul') {
            $channelPreference = 'alterno';
        }
        $channel = $this->resolveDeliveryChannel($channelPreference);

        if (! $channel) {
            return response()->json([
                'success' => false,
                'message' => $channelPreference === 'alterno'
                    ? 'No hay un canal WhatsApp alterno configurado para salida.'
                    : 'No hay un canal WhatsApp verde configurado para salida.',
            ], 422);
        }

        $empleado = Auth::user();
        $mensaje = trim($validated['mensaje']);

        // 1. Enviar por Evolution API (si falla aquí, el mensaje no salió)
        try {
            $dispatch = $this->whatsAppOutboundService->sendText(
                $telefonoNormalizado,
                $mensaje,
                $channel->instance_name,
                $channel->api_key,
                $channel->server_url,
            );
        } catch (\Throwable $e) {
            Log::error('Excepción al enviar mensaje por Evolution API', [
                'error' => $e->getMessage(),
                'telefono' => $validated['telefono'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al conectar con WhatsApp.',
            ], 500);
        }

        if (!($dispatch['ok'] ?? false)) {
            Log::warning('Evolution API no confirmó envío de mensaje al cliente', [
                'telefono' => $validated['telefono'],
                'channel_id' => $channel->id,
                'instance' => $channel->instance_name,
                'error' => $dispatch['error'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'WhatsApp no confirmó el envío del mensaje.',
            ], 422);
        }

        // 2. Persistir en BD (si falla, el mensaje ya llegó — loguear y seguir)
        $registro = null;
        try {
            $registro = $this->persistDeliveryOutbound(
                telefonoNormalizado: $telefonoNormalizado,
                telefonoOriginal: $validated['telefono'],
                mensaje: $mensaje,
                empleado: $empleado,
                channel: $channel,
                clienteId: $validated['id_cliente'] ?? null,
                clienteNombre: $validated['cliente'] ?? 'Cliente',
                dispatch: $dispatch,
            );
        } catch (\Throwable $e) {
            Log::error('Error persistiendo mensaje enviado en BD (mensaje WA ya entregado)', [
                'error' => $e->getMessage(),
                'telefono' => $validated['telefono'],
            ]);
        }

        try {
            Historial::create([
                'accion' => 'Mensaje directo a cliente',
                'descripcion' => 'Cliente: ' . ($validated['cliente'] ?? 'Cliente') . ' | Teléfono: ' . $validated['telefono'] . ' | Canal: ' . ($channel->display_name ?: $channel->instance_name),
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar historial de mensaje enviado', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente al cliente por ' . ($channel->display_name ?: $channel->instance_name) . '.',
            'data' => [
                'idconv' => $registro['conversacion']->idconv ?? null,
                'idmsg' => $registro['mensaje']->idmsg ?? null,
                'channel' => $channel->display_name ?: $channel->instance_name,
            ],
        ]);
    }

    private function resolveDeliveryChannel(string $preference): ?ChatWhatsappChannel
    {
        $query = ChatWhatsappChannel::query()->availableForOutbound();

        if ($preference === 'alterno') {
            return $query
                ->where('color', '!=', 'verde')
                ->orderByRaw("CASE WHEN color = 'azul' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->first();
        }

        return $query
            ->where('color', 'verde')
            ->orderBy('id')
            ->first();
    }

    private function persistDeliveryOutbound(
        string $telefonoNormalizado,
        string $telefonoOriginal,
        string $mensaje,
        $empleado,
        ChatWhatsappChannel $channel,
        $clienteId,
        string $clienteNombre,
        array $dispatch
    ): array {
        return DB::transaction(function () use (
            $telefonoNormalizado,
            $telefonoOriginal,
            $mensaje,
            $empleado,
            $channel,
            $clienteId,
            $clienteNombre,
            $dispatch
        ) {
            $contacto = ChatContactoCanal::query()->firstOrCreate(
                [
                    'canal' => 'whatsapp',
                    'canal_user_id' => $telefonoNormalizado,
                ],
                [
                    'telefono_normalizado' => $telefonoNormalizado,
                    'telefono' => $telefonoOriginal,
                    'nombre' => $clienteNombre,
                    'idcli' => $clienteId,
                    'estado_relacion' => $clienteId ? 'cliente' : 'lead',
                    'origen' => 'delivery-modal',
                    'metadata' => [
                        'instance' => $channel->instance_name,
                        'server_url' => $channel->server_url,
                        'whatsapp_channel_id' => $channel->id,
                    ],
                    'last_seen_at' => now(),
                ]
            );

            $contactMetadata = array_merge($contacto->metadata ?? [], [
                'instance' => $channel->instance_name,
                'server_url' => $channel->server_url,
                'whatsapp_channel_id' => $channel->id,
            ]);

            $contacto->fill([
                'telefono_normalizado' => $telefonoNormalizado,
                'telefono' => $telefonoOriginal,
                'nombre' => $clienteNombre,
                'idcli' => $clienteId ?: $contacto->idcli,
                'estado_relacion' => ($clienteId ?: $contacto->idcli) ? 'cliente' : 'lead',
                'metadata' => $contactMetadata,
                'last_seen_at' => now(),
            ])->save();

            $conversacion = Conversacion::query()->firstOrCreate(
                ['canal_contacto_id' => $contacto->id],
                [
                    'idcli' => $clienteId,
                    'canal_principal' => 'whatsapp',
                    'origen' => 'delivery-modal',
                    'estado' => 'atendiendo',
                    'ultima_actividad' => now(),
                    'last_message_at' => now(),
                    'mensajes_no_leidos' => 0,
                    'unread_count' => 0,
                    'requiere_humano' => false,
                ]
            );

            $conversationMetadata = array_merge($conversacion->metadata ?? [], [
                'instance' => $channel->instance_name,
                'server_url' => $channel->server_url,
                'whatsapp_channel_id' => $channel->id,
            ]);

            $conversacion->fill([
                'idcli' => $clienteId ?: $conversacion->idcli,
                'estado' => 'atendiendo',
                'ultimo_idemp' => $empleado->idemp,
                'ultima_actividad' => now(),
                'last_message_at' => now(),
                'metadata' => $conversationMetadata,
            ])->save();

            $mensajeModel = Mensaje::create([
                'idconv' => $conversacion->idconv,
                'tipo_remitente' => 'empleado',
                'idemp' => $empleado->idemp,
                'contenido' => $mensaje,
                'tipo_contenido' => 'texto',
                'leido' => true,
                'delivered_at' => ($dispatch['ok'] ?? false) ? now() : null,
                'error_message' => ($dispatch['ok'] ?? false) ? null : ($dispatch['error'] ?? 'No se pudo enviar a WhatsApp'),
                'metadata' => [
                    'source' => 'delivery-modal',
                    'channel_id' => $channel->id,
                    'channel_color' => $channel->color,
                    'channel_instance' => $channel->instance_name,
                ],
            ]);

            $canalMensaje = ChatMensajeCanal::create([
                'idmsg' => $mensajeModel->idmsg,
                'idconv' => $conversacion->idconv,
                'contacto_canal_id' => $contacto->id,
                'canal' => 'whatsapp',
                'direccion' => 'outbound',
                'external_message_id' => $dispatch['external_message_id'] ?? null,
                'external_status' => ($dispatch['ok'] ?? false) ? 'sent' : 'failed',
                'payload' => [
                    'source' => 'delivery-modal',
                    'instance' => $channel->instance_name,
                    'server_url' => $channel->server_url,
                    'whatsapp_channel_id' => $channel->id,
                    'dispatch' => [
                        'ok' => $dispatch['ok'] ?? false,
                        'error' => $dispatch['error'] ?? null,
                        'external_message_id' => $dispatch['external_message_id'] ?? null,
                    ],
                ],
            ]);

            return [
                'contacto' => $contacto,
                'conversacion' => $conversacion,
                'mensaje' => $mensajeModel,
                'canal_mensaje' => $canalMensaje,
            ];
        });
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
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar usuarios.'], 403);
            }
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        $detalle = DetalleVenta::findOrFail($iddet);
        $detalle->activodet = !$detalle->activodet;
        $detalle->save();

        Historial::create([
            'accion' => 'Cuenta-Quitada',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Usuario que se quitó: ' . json_encode($detalle),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Usuario eliminado con éxito.', 'iddet' => $iddet]);
        }

        return redirect()->back()->with('success', 'Usuario eliminado con éxito.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('usuarios', []);
        $ids = array_filter($ids, 'is_numeric');
        if (!Gate::allows('usuarios.destroy')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar usuarios.'], 403);
            }
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Usuarios eliminados correctamente.', 'ids' => array_values($ids)]);
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
