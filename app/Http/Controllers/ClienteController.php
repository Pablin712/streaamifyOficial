<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Models\ViewClientesUsuarios;
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

    public function create()
    {
        $this->authorizeRole(['administrador', 'vendedor']);
        return view('sales.clientes.create');
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:15|unique:clientes,telefonocli'
        ]);

        // Formatear el nombre del cliente
        $request->merge([
            'nombrecli' => ucwords(strtolower($request->nombrecli))
        ]);

        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();

        if ($clienteExistente) {
            return redirect()->route('clientes.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }

        Cliente::create($request->all());

        return redirect()->route('clientes')->with('success', 'Cliente creado con éxito.');
    }

    public function edit($idcli)
    {
        $this->authorizeRole(['administrador', 'vendedor', 'tecnico']);

        $cliente = Cliente::findOrFail($idcli);
        return view('sales.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $idcli)
    {
        $this->authorizeRole(['administrador', 'vendedor', 'tecnico']);

        $request->validate([
            'nombrecli' => 'required|string|max:20',
            'telefonocli' => 'nullable|string|max:15'
        ]);

        $cliente = Cliente::findOrFail($idcli);
        $cliente->update($request->all());

        return redirect()->route('clientes')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy($idcli)
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $cliente = Cliente::findOrFail($idcli);
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

