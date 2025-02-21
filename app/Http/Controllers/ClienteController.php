<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ViewClientesUsuarios;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
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

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('clientes')) {
            abort(403, 'No tienes permiso para ver los clientes.');
        }
        $clientes = Cliente::with('viewClienteUsuario')->orderBy('created_at', 'desc')->get();
        $autenticados = Cliente::whereNotNull('email')
            ->whereNotNull('password')
            ->count();
        return view('sales.clientes.index', compact('clientes', 'autenticados'));
    }

    // Mostrar formulario para crear un cliente (para el caso general)
    public function create()
    {
        if (!Auth::user()->hasPermissionTo('clientes.store')) {
            abort(403, 'No tienes permiso para crear clientes.');
        }
        return view('sales.clientes.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('clientes.store')) {
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
            'realizado_por' => (Auth::user()->nombreemp ?? 'laravel') . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);
        return redirect()->route('clientes')->with('success', 'Cliente creado con éxito.');
    }

    // Método para crear cliente desde vista de venta
    public function storeInVenta(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('clientes.storeInVenta')) {
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
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }
        $cliente = Cliente::create($request->all());
        Historial::create([
            'accion' => 'Creación de cliente desde vista Ventas',
            'descripcion' => 'Datos: ' . json_encode($cliente),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);
        return redirect()->route('ventas.create')->with('success', 'Cliente creado correctamente.')->with('cliente', $cliente);
    }

    // Mostrar formulario para editar cliente
    public function edit($idcli)
    {
        if (!Auth::user()->hasPermissionTo('clientes.update')) {
            abort(403, 'No tienes permiso para editar clientes.');
        }
        $cliente = Cliente::findOrFail($idcli);
        return view('sales.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $idcli)
    {
        if (!Auth::user()->hasPermissionTo('clientes.update')) {
            abort(403, 'No tienes permiso para actualizar clientes.');
        }
        $request->validate([
            'nombrecli' => 'required|string|max:20',
            'telefonocli' => 'nullable|string|max:25'
        ]);
        $request->merge([
            'nombrecli' => ucwords($request->nombrecli)
        ]);
        $cliente = Cliente::findOrFail($idcli);
        Historial::create([
            'accion' => 'Actualización de cliente',
            'descripcion' => 'Datos antiguos: ' . json_encode($cliente),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);
        $cliente->update($request->all());
        return redirect()->route('clientes')->with('success', 'Cliente actualizado con éxito.');
    }

    // Eliminar un cliente
    public function destroy($idcli)
    {
        if (!Auth::user()->hasPermissionTo('clientes.destroy')) {
            abort(403, 'No tienes permiso para eliminar clientes.');
        }
        $cliente = Cliente::findOrFail($idcli);
        Historial::create([
            'accion' => 'Eliminación de cliente',
            'descripcion' => 'Datos borrados: ' . json_encode($cliente),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);
        $cliente->delete();
        return redirect()->route('clientes')->with('success', 'Cliente eliminado con éxito.');
    }

    public function register(Request $request)
    {
        // Registro de cliente desde vista pública (sin protección de permisos)
        try {
            $request->validate(
                [
                    'first_name' => ['required', 'regex:/^\S+\s+\S+$/'],
                    'last_name' => ['required', 'regex:/^\S+(?:\s+\S+)*$/'],
                    'email' => 'required|email|unique:clientes,email',
                    'telefonocli' => 'required',
                    'pais' => 'required',
                    'password' => [
                        'required',
                        'confirmed',
                        'min:6',
                        'regex:/[0-9]/',
                        'regex:/[@$!%*?&]/'
                    ],
                ],
                [
                    'first_name.regex' => 'Debe ingresar al menos dos nombres.',
                    'last_name.regex' => 'Debe ingresar al menos un apellido.',
                    'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
                    'password.regex' => 'La contraseña debe contener al menos un número y un símbolo especial (@$!%*?&).',
                    'password.confirmed' => 'Las contraseñas no coinciden.',
                ]
            );
            $request->merge([
                'first_name' => ucwords($request->first_name),
                'last_name' => ucwords($request->last_name),
                'pais' => ucwords($request->pais)
            ]);
            $cliente = Cliente::where('telefonocli', $request->telefonocli)->first();
            if ($cliente) {
                $cliente->update([
                    'nombrecli' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'pais' => $request->pais,
                ]);
                Historial::create([
                    'accion' => 'Registro de cliente con correo',
                    'descripcion' => 'Datos: ' . json_encode($cliente),
                    'realizado_por' => 'laravel' . ' | ' . $request->ip(),
                    'fecha' => now(),
                ]);
                return redirect()->route('cliente.login')->with('success', '¡Tu cuenta ha sido registrada exitosamente!');
            } else {
                $cliente = Cliente::create([
                    'nombrecli' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'telefonocli' => $request->telefonocli,
                    'pais' => $request->pais,
                    'saldo' => 0,
                ]);
                Historial::create([
                    'accion' => 'Creación de cliente automático',
                    'descripcion' => 'Datos: ' . json_encode($cliente),
                    'realizado_por' => 'laravel' . ' | ' . $request->ip(),
                    'fecha' => now(),
                ]);
                return redirect()->route('cliente.login')->with('success', '¡Cuenta creada exitosamente!');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Por favor, corrige los errores en el formulario.');
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
            'nombrecli' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(?: [A-Za-zÁÉÍÓÚáéíóúñÑ]+){3,}$/'],
            'telefonocli' => ['required', 'digits:10'],
            'email' => ['required', 'email', 'unique:clientes,email,' . $cliente->idcli . ',idcli'],
        ], [
            'nombrecli.regex' => 'Debe ingresar sus dos nombres y apellidos correctamente.',
            'telefonocli.digits' => 'Ingrese un número de teléfono válido de 10 dígitos.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $cliente->update([
            'nombrecli' => $request->nombrecli,
            'telefonocli' => $request->telefonocli,
            'email' => $request->email,
        ]);
        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                'min:6',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/'
            ],
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