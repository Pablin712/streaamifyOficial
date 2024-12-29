<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Models\ViewClientesUsuarios;

use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
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
            'accion' => 'Se creo el cliente con ID: ' . $cliente->idcli,
            'descripcion' =>  'Datos: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
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
            'accion' => 'Se creo el cliente con ID: ' . $cliente->idcli,
            'descripcion' =>  'Datos: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
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
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
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
            'accion' => 'Se eliminó el cliente con ID: ' . $idcli,
            'descripcion' => 'Datos borrados: ' . json_encode($cliente), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        $cliente->delete();

        return redirect()->route('clientes')->with('success', 'Cliente eliminado con éxito.');
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
