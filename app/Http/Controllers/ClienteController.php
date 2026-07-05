<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ViewClientesUsuarios;
use App\Models\Historial;
use App\Support\ClienteAuth;
use App\Services\ClienteMensajeMasivoService;
use App\Services\ConcentracionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClienteController extends Controller
{
    public function __construct(private ClienteMensajeMasivoService $clienteMensajeMasivoService)
    {
    }

    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:clientes')->only('index');
        $this->middleware('can:clientes.store')->only('create', 'store');
        $this->middleware('can:clientes.storeInVenta')->only('storeInVenta');
        $this->middleware('can:clientes.update')->only('edit', 'update');
        $this->middleware('can:clientes.destroy')->only('destroy');
    }
    */

    public function index(Request $request)
    {
        // Informar a IDE para que no muestre errores con Auth::user()
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes')) {
            abort(403, 'No tienes permiso para ver los clientes.');
        }

        // Si es petición AJAX, retornar datos paginados
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getClientesAjax($request);
        }

        $isLocked = ConcentracionService::isLocked();

        $autenticados = $isLocked ? null : Cliente::whereNotNull('email')->whereNotNull('password')->count();
        $segmentosMensajeMasivo = $isLocked ? [] : $this->clienteMensajeMasivoService->getSegmentSummary();

        return view('sales.clientes.index', compact('autenticados', 'segmentosMensajeMasivo', 'isLocked'));
    }

    /**
     * Obtener clientes paginados para AJAX
     */
    private function allowedClientIds(): ?array
    {
        if (! ConcentracionService::isActive()) {
            return null;
        }
        $ids = app(ConcentracionService::class)->getIds(Auth::user()->idemp);
        if ($ids['all_clientes'] ?? false) {
            return null;
        }
        return $ids['idcli'];
    }

    private function getClientesAjax(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Cliente::with('viewClienteUsuario');

        $allowedIds = $this->allowedClientIds();
        if ($allowedIds !== null) {
            if (empty($allowedIds)) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('idcli', $allowedIds);
            }
        }

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombrecli', 'like', "%{$search}%")
                    ->orWhere('telefonocli', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('idcli', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $validSortColumns = ['idcli' => 'idcli', 'nombrecli' => 'nombrecli', 'created_at' => 'created_at'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $totalRecords = $query->count();
        $clientes = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $html = view('sales.clientes.partials.table-rows', compact('clientes'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
    }

    // Mostrar formulario para crear un cliente (para el caso general)
    public function create()
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.store')) {
            abort(403, 'No tienes permiso para crear clientes.');
        }
        return view('sales.clientes.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.store')) {
            abort(403, 'No tienes permiso para crear clientes.');
        }
        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:50|unique:clientes,telefonocli'
        ]);
        $request->merge([
            'nombrecli' => ucwords($request->nombrecli)
        ]);
        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();
        if ($clienteExistente) {
            return redirect()->route('clientes.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }
        $cliente = Cliente::create($request->all());
        Historial::create([
            'accion' => 'Creación de cliente',
            'descripcion' => 'Datos: ' . json_encode($cliente),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado con éxito',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('clientes')->with('success', 'Cliente creado con éxito.');
    }

    // Método para crear cliente desde vista de venta
    public function storeInVenta(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.storeInVenta')) {
            abort(403, 'No tienes permiso para crear clientes desde ventas.');
        }
        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:50|unique:clientes,telefonocli'
        ]);
        $request->merge([
            'nombrecli' => ucwords($request->nombrecli)
        ]);
        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();
        if ($clienteExistente) {
            // Triple verificación AJAX para errores
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este cliente ya existe. Verifica los valores de nombre o teléfono.'
                ], 422);
            }
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }
        $cliente = Cliente::create($request->all());
        Historial::create([
            'accion' => 'Creación de cliente desde vista Ventas',
            'descripcion' => 'Datos: ' . json_encode($cliente),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado con éxito',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('ventas.create')->with('success', 'Cliente creado correctamente.')->with('cliente', $cliente);
    }

    // Mostrar formulario para editar cliente
    public function edit($idcli)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.update')) {
            abort(403, 'No tienes permiso para editar clientes.');
        }
        $cliente = Cliente::findOrFail($idcli);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ]);
        }

        return view('sales.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $idcli)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.update')) {
            abort(403, 'No tienes permiso para actualizar clientes.');
        }
        $request->validate([
            'nombrecli' => 'required|string|max:50',
            'telefonocli' => 'nullable|string|max:25'
        ]);
        $request->merge([
            'nombrecli' => ucwords($request->nombrecli)
        ]);
        $cliente = Cliente::findOrFail($idcli);
        Historial::create([
            'accion' => 'Actualización de cliente',
            'descripcion' => 'Datos antiguos: ' . json_encode($cliente),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $cliente->update($request->all());

        // Triple verificación AJAX
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado con éxito',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('clientes')->with('success', 'Cliente actualizado con éxito.');
    }

    public function export()
    {
        $clientes = Cliente::all();

        $response = new StreamedResponse(function () use ($clientes) {
            $handle = fopen('php://output', 'w');
            // Add CSV headers
            fputcsv($handle, ['ID', 'Nombre', 'Teléfono', 'Correo']);

            // Add client data
            foreach ($clientes as $cliente) {
                fputcsv($handle, [
                    $cliente->idcli,
                    $cliente->nombrecli,
                    $cliente->telefonocli,
                    $cliente->email ?? 'Ninguno',
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="clientes.csv"');

        return $response;
    }

    public function enviarMensajeMasivo(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes')) {
            abort(403, 'No tienes permiso para enviar mensajes masivos a clientes.');
        }

        $validated = $request->validate([
            'segmento' => 'required|string|in:sin_web,con_web',
            'mensaje' => 'required|string|min:20|max:4000',
        ]);

        $payload = $this->clienteMensajeMasivoService->buildWebhookPayload($validated['segmento'], $validated['mensaje'], $user);

        if (empty($payload['clientes'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron clientes activos con telefono valido para enviar el mensaje.',
            ], 422);
        }

        $webhookUrl = config('services.n8n.mass_client_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'No esta configurado el webhook de mensajes masivos.',
            ], 500);
        }

        try {
            $requestN8n = Http::acceptJson()
                ->timeout(15)
                ->retry(1, 300);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook n8n de mensaje masivo a clientes devolvio error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'clientes_count' => $payload['clientes_count'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar el mensaje masivo al webhook.',
                ], 502);
            }

            Historial::create([
                'accion' => 'Mensaje masivo a clientes activos',
                'descripcion' => 'Clientes: ' . $payload['clientes_count'] . ' | Segmento: ' . ($payload['segment'] ?? 'general') . ' | Tipo: ' . ($payload['message_type'] ?? 'masivo'),
                'empleado_id' => $user->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje masivo enviado correctamente.',
                'clientes_count' => $payload['clientes_count'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando mensaje masivo a clientes activos', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al enviar el mensaje masivo.',
            ], 500);
        }
    }

    // Eliminar un cliente
    public function destroy($idcli)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('clientes.destroy')) {
            abort(403, 'No tienes permiso para eliminar clientes.');
        }
        $cliente = Cliente::findOrFail($idcli);
        Historial::create([
            'accion' => 'Eliminación de cliente',
            'descripcion' => 'Datos borrados: ' . json_encode($cliente),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $cliente->delete();

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado con éxito'
            ]);
        }

        return redirect()->route('clientes')->with('success', 'Cliente eliminado con éxito.');
    }

    public function register(Request $request)
    {
        // Registro de cliente desde vista pública (sin protección de permisos)
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => ['required', 'string', 'regex:/^\S+\s+\S+(?:\s+\S+)*$/'],
                    'last_name' => ['required', 'string', 'regex:/^\S+(?:\s+\S+)*$/'],
                    'email' => 'required|email|unique:clientes,email',
                    'telefonocli' => 'required|string|max:' . ClienteAuth::MAX_PHONE_LENGTH,
                    'pais' => 'required|string|max:' . ClienteAuth::MAX_COUNTRY_LENGTH,
                    'password' => ClienteAuth::passwordRules(),
                    'codigo_referidor' => 'nullable|string|max:50|exists:clientes,codigo_referidor',
                ],
                array_merge([
                    'first_name.regex' => 'Debe ingresar al menos dos nombres.',
                    'last_name.regex' => 'Debe ingresar al menos un apellido.',
                ], ClienteAuth::passwordMessages())
            );

            $nombreCompleto = ClienteAuth::buildFullName($request->first_name, $request->last_name);

            $validator->after(function ($validator) use ($nombreCompleto) {
                if (mb_strlen($nombreCompleto) > ClienteAuth::MAX_FULL_NAME_LENGTH) {
                    $validator->errors()->add('first_name', ClienteAuth::fullNameTooLongMessage());
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            // Buscar el cliente que refirió (si se ingresó un código válido)
            $referidoPor = null;
            if ($request->filled('codigo_referidor')) {
                $referidor = Cliente::where('codigo_referidor', $request->codigo_referidor)->first();
                if ($referidor) {
                    $referidoPor = $referidor->idcli;
                }
            }
            $telefono = ClienteAuth::normalizePhone($request->telefonocli);
            $pais = ClienteAuth::normalizeName($request->pais);
            $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);

            if ($cliente) {
                if ($cliente->email) {
                    return redirect()->back()
                        ->withErrors(['telefonocli' => 'Este número de teléfono ya está registrado.'])
                        ->withInput()
                        ->with('error', 'Este número de teléfono ya está registrado.');
                }

                $cliente->update([
                    'nombrecli' => $nombreCompleto,
                    'email' => $request->email,
                    'password' => $request->password,
                    'telefonocli' => $telefono,
                    'pais' => $pais,
                    'referido_por' => $referidoPor,
                ]);

                return redirect()->route('cliente.login')->with('success', '¡Tu cuenta ha sido registrada exitosamente!');
            }

            Cliente::create([
                'nombrecli' => $nombreCompleto,
                'email' => $request->email,
                'password' => $request->password,
                'telefonocli' => $telefono,
                'pais' => $pais,
                'saldo' => 0,
                'referido_por' => $referidoPor,
            ]);

            return redirect()->route('cliente.login')->with('success', '¡Cuenta creada exitosamente!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Por favor, corrige los errores en el formulario.');
        } catch (\Throwable $e) {
            Log::error('Error en registro de cliente', [
                'email' => $request->email,
                'telefono' => $request->telefonocli,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'No se pudo completar el registro. Verifica los datos e intenta nuevamente.');
        }
    }

    public function perfil()
    {
        $cliente = Auth::guard('cliente')->user();
        return view('shopping.perfil', compact('cliente'));
    }

    public function actualizarPerfil(Request $request)
    {
        $idCliente = Auth::guard('cliente')->user()->idcli;
        $cliente = Cliente::findOrFail($idCliente);
        $validator = Validator::make($request->all(), [
            'nombrecli' => ['required', 'string', 'max:' . ClienteAuth::MAX_FULL_NAME_LENGTH, 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(?: [A-Za-zÁÉÍÓÚáéíóúñÑ]+){3,}$/'],
            'telefonocli' => ['required', 'string', 'max:' . ClienteAuth::MAX_PHONE_LENGTH],
            'email' => ['required', 'email', 'unique:clientes,email,' . $cliente->idcli . ',idcli'],
        ], [
            'nombrecli.regex' => 'Debe ingresar sus dos nombres y apellidos correctamente.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $cliente->update([
            'nombrecli' => ClienteAuth::normalizeName($request->nombrecli),
            'telefonocli' => ClienteAuth::normalizePhone($request->telefonocli),
            'email' => $request->email,
        ]);
        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ClienteAuth::passwordRules(),
        ], [
            'new_password.regex' => 'La nueva contraseña debe contener al menos un número y un símbolo especial (@$!%*?&).',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'new_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);
        $idCliente = Auth::guard('cliente')->user()->idcli;
        $cliente = Cliente::findOrFail($idCliente);
        if (!Hash::check($request->current_password, $cliente->password)) {
            return redirect()->back()->with('error', 'La contraseña actual es incorrecta.');
        }
        $cliente->update([
            'password' => $request->new_password,
        ]);
        return redirect()->back()->with('success', '¡Contraseña actualizada exitosamente!');
    }

    public function indexApi(Request $request)
    {
        $clientes = Cliente::all();
        foreach ($clientes as $cliente) {
            $usuarios = ViewClientesUsuarios::where('idcli', $cliente->idcli)->first();
            if ($usuarios) {
                $cliente->usuarios = $usuarios->usuarios;
                $cliente->facturado = $usuarios->facturado;
            } else {
                $cliente->usuarios = 0;
                $cliente->facturado = 0;
            }
        }
        return response()->json(['clientes' => $clientes]);
    }
}
