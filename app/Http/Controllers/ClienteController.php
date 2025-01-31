<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Models\ViewClientesUsuarios;

use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index()
    {

        $this->authorizeRole(['administrador', 'vendedor', 'tecnico']);

        $clientes = Cliente::all();
        foreach ($clientes as $cliente) {
            $usuarios = ViewClientesUsuarios::where('idcli', $cliente->idcli)->first();
            if ($usuarios) {
                $cliente->usuarios = $usuarios->usuarios; // Asignar el número de usuarios
                $cliente->facturado = $usuarios->facturado; // Asignar el total facturado
            } else {
                $cliente->usuarios = 0; // Si no tiene registros, asignar 0
                $cliente->facturado = 0; // Si no tiene registros, asignar 0
            }
        }
        return view('sales.clientes.index', compact('clientes'));
    }
    // Crear un nuevo proveedor
    public function create()
    {
        $this->authorizeRole(['administrador', 'vendedor', 'tecnico']);
        return view('sales.clientes.create');
    }

    public function store(Request $request)
    {
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

        // Si el cliente ya existe, redirigir con mensaje de error
        if ($clienteExistente) {
            return redirect()->route('clientes.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }

        $cliente = Cliente::create($request->all());

        Historial::create([
            'accion' => 'Se creo el cliente con ID: ' . $cliente->idcli . ' y su nombre es ' . $cliente->nombrecli,
            'descripcion' =>  'Datos: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        return redirect()->route('clientes')->with('success', 'Cliente creado con éxito.');
    }

    public function storeInVenta(Request $request)
    {
        // Validar los datos
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

        // Si el cliente ya existe, redirigir con mensaje de error
        if ($clienteExistente) {
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }

        // Crear un nuevo cliente
        $cliente = Cliente::create($request->all());

        Historial::create([
            'accion' => 'Se creo el cliente con ID: ' . $cliente->idcli . ' y su nombre es ' . $cliente->nombrecli,
            'descripcion' =>  'Datos: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        return redirect()->route('ventas.create')->with('success', 'Cliente creado correctamente.')->with('cliente', $cliente);;
    }

    // Editar un cliente existente
    public function edit($idcli)
    {
        $cliente = Cliente::findOrFail($idcli);
        return view('sales.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $idcli)
    {
        $request->validate([
            'nombrecli' => 'required|string|max:20', // varchar(20)
            'telefonocli' => 'nullable|string|max:25'
        ]);
        $request->merge([
            'nombrecli' => ucwords($request->nombrecli)
        ]);

        $cliente = Cliente::findOrFail($idcli);

        Historial::create([
            'accion' => 'Se actualizo datos de el cliente con ID: ' . $idcli,
            'descripcion' =>  'Datos antiguos: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes')->with('success', 'Cliente actualizado con éxito.');
    }

    // Eliminar un cliente
    public function destroy($idcli)
    {
        $cliente = Cliente::findOrFail($idcli);

        Historial::create([
            'accion' => 'Se eliminó el cliente con ID: ' . $idcli . ' y su nombre es ' . $cliente->nombrecli,
            'descripcion' => 'Datos borrados: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        $cliente->delete();

        return redirect()->route('clientes')->with('success', 'Cliente eliminado con éxito.');
    }
    public function register(Request $request)
    {
        try {
            // Validación de los datos del formulario
            $request->validate(
                [
                    'first_name' => ['required', 'regex:/^\S+\s+\S+$/'],
                    'last_name' => ['required', 'regex:/^\S+\s+\S+$/'],
                    'email' => 'required|email|unique:clientes,email', // Email único
                    'telefonocli' => 'required',
                    'pais' => 'required',
                    'password' => [
                        'required',
                        'confirmed',
                        'min:6', // Mínimo de 6 caracteres
                        'regex:/[0-9]/', // Al menos un número
                        'regex:/[@$!%*?&]/', // Al menos un símbolo especial
                    ],
                ],
                [
                    'first_name.regex' => 'Debe ingresar al menos dos nombres.',
                    'last_name.regex' => 'Debe ingresar al menos dos apellidos.',
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
            // 🔹 Buscar si el cliente ya existe por número de teléfono
            $cliente = Cliente::where('telefonocli', $request->telefonocli)->first();

            if ($cliente) {
                // 🔹 Si el cliente existe, actualizar su información
                $cliente->update([
                    'nombrecli' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => $request->password, // Ya está encriptada
                    'pais' => $request->pais,
                ]);

                return redirect()->route('cliente.login')->with('success', '¡Tu cuenta ha sido registrada exitosamente!');
            } else {
                // 🔹 Si el cliente NO existe, crear un nuevo registro
                Cliente::create([
                    'nombrecli' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'password' => $request->password, // Ya está encriptada
                    'telefonocli' => $request->telefonocli,
                    'pais' => $request->pais,
                    'saldo' => 0, // Saldo inicial en 0
                ]);
    
                return redirect()->route('cliente.login')->with('success', '¡Cuenta creada exitosamente!');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si hay errores de validación, redirigir con los errores y datos antiguos
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
        // Buscar el cliente en la base de datos
        $cliente = Cliente::findOrFail($idCliente);
        // Validaciones
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

        // Actualizar datos del cliente
        $cliente->update([
            'nombrecli' => $request->nombrecli,
            'telefonocli' => $request->telefonocli,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
    private function authorizeRole(array $roles)
    {
        $userRole = Auth::user()->idrol;

        if (!in_array($userRole, $roles)) {
            // Redirigir a la vista anterior con una alerta
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }
}
