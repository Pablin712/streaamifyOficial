<?php

namespace App\Http\Controllers;

use App\Models\ChatContactoCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Cliente;
use App\Models\Conversacion;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Soporte;
use App\Services\Chat\WhatsAppOutboundService;
use App\Services\ConcentracionService;
use App\Services\TareaService;
use App\Models\ViewUsuarioActivo;
use App\Notifications\NuevoSoporteCliente;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if (ConcentracionService::isActive()) {
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

        app(TareaService::class)->completarTareasRelacionadas('soporte_pendiente', 'Soporte', $idsop, $user->idemp);

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

    public function enviarWhatsapp(Request $request, $idsop)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('soportes.update')) {
            abort(403, 'No tienes permiso para enviar mensajes de soporte.');
        }

        $soporte = Soporte::with(['cliente', 'cuenta'])->findOrFail($idsop);

        if ($soporte->estado !== 'atendido' || !$soporte->solucion) {
            return response()->json([
                'success' => false,
                'message' => 'El soporte debe estar atendido con una solución registrada.',
            ], 422);
        }

        try {
            ['ok' => $ok, 'error' => $error] = $this->enviarMensajeSolucion($soporte, $user);
        } catch (\Throwable $e) {
            Log::error('Excepción en enviarMensajeSolucion', ['idsop' => $idsop, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno al intentar enviar el WhatsApp.',
            ], 500);
        }

        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => $error ?: 'No se pudo enviar el mensaje por WhatsApp.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mensaje de solución enviado por WhatsApp.',
            'whatsapp_enviado_at' => $soporte->fresh()->whatsapp_enviado_at?->toDateTimeString(),
        ]);
    }

    private function enviarMensajeSolucion(Soporte $soporte, Empleado $empleado): array
    {
        $cliente = $soporte->cliente;

        if (!$cliente) {
            return ['ok' => false, 'error' => 'El soporte no tiene cliente asociado.'];
        }

        // Buscar el último contacto del cliente por WhatsApp para obtener instancia y número
        $contactoCanal = ChatContactoCanal::where('idcli', $cliente->idcli)
            ->where('canal', 'whatsapp')
            ->orderByDesc('last_seen_at')
            ->first();

        $telefono = null;
        $channel = null;

        if ($contactoCanal) {
            $telefono = $contactoCanal->canal_user_id ?: $contactoCanal->telefono_normalizado;
            $meta = is_array($contactoCanal->metadata) ? $contactoCanal->metadata : [];

            $channelId = $meta['whatsapp_channel_id'] ?? null;
            $instance = $meta['instance'] ?? null;

            if ($channelId) {
                $channel = ChatWhatsappChannel::find($channelId);
            }

            if (!$channel && $instance) {
                $channel = app(WhatsAppOutboundService::class)->resolveChannelByInstance($instance);
            }
        }

        // Fallback: teléfono del cliente + canal verde por defecto
        if (!$telefono && $cliente->telefonocli) {
            $telefono = preg_replace('/\D+/', '', $cliente->telefonocli);
        }

        if (!$channel) {
            $channel = ChatWhatsappChannel::availableForOutbound()
                ->where('color', 'verde')
                ->orderBy('id')
                ->first();
        }

        if (!$telefono) {
            return ['ok' => false, 'error' => 'El cliente no tiene teléfono registrado.'];
        }

        if (!$channel) {
            return ['ok' => false, 'error' => 'No hay canal WhatsApp disponible para enviar.'];
        }

        $mensaje = "Hola {$cliente->nombrecli} 👋\n\n"
            . "Tu soporte #{$soporte->idsop} ha sido atendido.\n\n"
            . "*Solución:*\n{$soporte->solucion}\n\n"
            . "Si tienes alguna duda, no dudes en escribirnos. 😊";

        try {
            $dispatch = app(WhatsAppOutboundService::class)->sendText(
                $telefono,
                $mensaje,
                $channel->instance_name,
                $channel->api_key,
                $channel->server_url,
            );
        } catch (\Throwable $e) {
            Log::error('Error enviando WhatsApp de soporte', [
                'idsop' => $soporte->idsop,
                'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'error' => 'Error al conectar con WhatsApp.'];
        }

        if (!($dispatch['ok'] ?? false)) {
            Log::warning('WhatsApp no confirmó envío de solución de soporte', [
                'idsop' => $soporte->idsop,
                'error' => $dispatch['error'] ?? null,
            ]);
            return ['ok' => false, 'error' => $dispatch['error'] ?? 'WhatsApp no confirmó el envío.'];
        }

        $soporte->update(['whatsapp_enviado_at' => now()]);

        try {
            Historial::create([
                'accion' => 'Mensaje WhatsApp de soporte',
                'descripcion' => "Solución del soporte #{$soporte->idsop} enviada por WhatsApp al cliente {$cliente->nombrecli}",
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no bloquear el flujo
        }

        return ['ok' => true, 'error' => null];
    }
}
