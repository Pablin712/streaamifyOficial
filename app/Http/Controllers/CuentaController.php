<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;

use Illuminate\Support\Facades\Auth;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico', 'vendedor']);
        $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get(); // Cargar valor asociado
        // Inicializar una colección vacía para los perfiles
        $perfiles = collect();

        $idcueSeleccionado = $request->idcue;

        //$usuariosActivos = ViewUsuarioActivo::where('IDCUE', $idcueSeleccionado)->get(); //por si acaso

        if ($idcueSeleccionado) {
            //$usuarioscuenta = Cuenta::where('idcue',$idcueSeleccionado)->get();
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
            foreach ($perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $idcueSeleccionado)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
        }

        // Pasar las cuentas y los perfiles a la vista
        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
    }

    // Mostrar formulario para crear una nueva cuenta contratada
    public function create()
    {

        $valores = Valor::all(); // Obtener lista de valores
        return view('inventory.cuentas.create', compact('valores'));
    }

    // Guardar una nueva cuenta
    public function store(Request $request)
    {
        $request->merge([
            'idcue' => strtoupper($request->idcue)
        ]);
        // Validar datos de la cuenta
        $validated = $request->validate([
            'idcue' => 'required|string|max:20|unique:cuentas,idcue',
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50|unique:cuentas,idcue',
            'contrasenacue' => 'required|string|min:8|max:50',
            'caidacue' => 'required|boolean',
        ]);

        // Crear la cuenta (otra alternativa)
        $cuenta = Cuenta::create($validated);
        // Comprobar si los datos de costo están presentes
        // Si hay campos de costo, validarlos y crear el costo
        if ($request->filled('descripcioncos') || $request->filled('montocos')) {
            $validatedCosto = $request->validate([
                'descripcioncos' => 'required|string|max:50',
                'montocos' => 'required|numeric|min:0',
            ]);

            // Crear el costo asociado a la cuenta
            Costo::create([
                'idcue' => $request->idcue, // Asociar el costo a la cuenta recién creada
                'fechacos' => now(),
                'montocos' => $validatedCosto['montocos'],
                'descripcioncos' => $validatedCosto['descripcioncos'],
            ]);
        }
        return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
    }
    // CuentaController.php
    public function status($idcue)
    {
        $cuenta = Cuenta::findOrFail($idcue);
        // Cambiar el estado de caidacue (de true a false o de false a true)
        $cuenta->caidacue = !$cuenta->caidacue; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $cuenta->save();

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }
    public function mensaje($perfilId)
    {
        // Obtener el perfil seleccionado
        $perfil = Perfil::find($perfilId);

        // Obtener la cuenta asociada al perfil
        $cuenta = Cuenta::where('idcue', $perfil->idcue)->first();

        // Obtener el valor asociado a la cuenta
        $valor = Valor::find($cuenta->idval);

        // Obtener el servicio asociado al valor
        $servicio = Servicio::find($valor->idser);

        // Construir el mensaje
        $mensaje = "<strong>{$servicio->nombre}</strong>\n";
        $mensaje .= "Usuario: {$cuenta->usuariocue}\n";
        $mensaje .= "Clave: {$cuenta->contrasenacue}\n";
        $mensaje .= "PIN de perfil {$perfil->numeroper}: ";
        $mensaje .= "{$perfil->pinper}\n";

        // Devolver el mensaje al frontend
        return response()->json(['mensaje' => $mensaje]);
    }

    // Mostrar formulario para editar una cuenta
    public function edit($idcue)
    {
        // Buscar la cuenta con la relacion valores
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::all();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        // Buscar la cuenta con la relacion valores
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valor = $cuenta->idval;
        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
    }

    // Actualizar una cuenta existente
    public function update(Request $request, $idcue)
    {
        $request->validate([
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50',
            'contrasenacue' => 'required|string|max:50',
            'caidacue' => 'required|boolean|min:1'
        ]);
        $request->merge([
            'idcue' => strtoupper($request->idcue)
        ]);

        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->update($request->all());

        if (!empty($request->descripcioncos) && !empty($request->montocos)) {
            // Validar datos de costo si los campos están presentes
            $validatedCosto = $request->validate([
                'descripcioncos' => 'string|max:50',
                'montocos' => 'numeric|min:0',
            ]);

            // Crear el costo asociado a la cuenta solo si los datos de costo están presentes
            Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),  // O la fecha que desees
            ]);
        }
        return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
    }

    // Eliminar una cuenta
    public function destroy($idcue)
    {
        $cuenta = Cuenta::findOrFail($idcue);
        // Verificar si los perfiles están registrados en detalles_venta
        foreach ($cuenta->perfiles as $perfil) {
            $perfilInDetalleVenta = DetalleVenta::where('idper', $perfil->idper)->exists();

            if ($perfilInDetalleVenta) {
                return redirect()->route('cuentas')->with('error', 'No se puede eliminar la cuenta porque uno o más perfiles están registrados en detalles_venta.');
            }
        }
        $cuenta->perfiles()->delete();
        $cuenta->delete();

        return redirect()->route('cuentas')->with('success', 'Cuenta eliminada con éxito.');
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
