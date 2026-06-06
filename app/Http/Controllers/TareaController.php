<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use App\Models\Tarea;
use App\Services\TareaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TareaController extends Controller
{
    protected TareaService $tareaService;

    public function __construct(TareaService $tareaService)
    {
        $this->tareaService = $tareaService;
    }

    public function index()
    {
        // Sincroniza tareas individuales del sistema
        $this->tareaService->sincronizarTareas();
        return view('employee.tareas');
    }

    public function destroy($id)
    {
        Gate::authorize('tareas.destroy');
        Tarea::findOrFail($id)->delete();
        return redirect()->route('tareas.index')->with('success', 'Tarea eliminada.');
    }

    public function enviarCobrosWspMasivo(Request $request)
    {
        if (! Gate::allows('usuarios.update') && ! Gate::allows('cuentas.mensaje')) {
            abort(403, 'Sin permiso para enviar mensajes masivos.');
        }

        $validated = $request->validate([
            'destinatarios'           => 'required|array|min:1|max:300',
            'destinatarios.*.nombre'  => 'nullable|string|max:255',
            'destinatarios.*.telefono'=> 'required|string|max:50',
            'destinatarios.*.idcli'   => 'nullable|integer',
            'destinatarios.*.mensaje' => 'required|string|min:3|max:4000',
        ]);

        $webhookUrl = config('services.n8n.mass_client_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json(['success' => false, 'message' => 'Webhook masivo no configurado.'], 500);
        }

        $empleado = Auth::user();
        $payload = [
            'event'         => 'tareas.cobros_wsp_masivo',
            'trace_id'      => 'cobros-wsp-' . now()->format('Ymd-His'),
            'destinatarios' => $validated['destinatarios'],
            'empleado'      => [
                'idemp'      => $empleado->idemp,
                'nombreemp'  => $empleado->nombreemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];

        try {
            $response = Http::acceptJson()->timeout(10)->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('Webhook n8n de cobros WSP masivo devolvió error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json(['success' => false, 'message' => 'El webhook no confirmó el envío.'], 502);
            }

            $count = count($validated['destinatarios']);

            Historial::create([
                'accion'      => 'Cobros WhatsApp masivo vía n8n',
                'descripcion' => "Destinatarios: {$count}",
                'empleado_id' => $empleado->idemp,
                'created_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Enviado a n8n: {$count} destinatario" . ($count !== 1 ? 's' : '') . '.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando cobros WSP masivo a n8n', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error de conexión con n8n.'], 500);
        }
    }
}
