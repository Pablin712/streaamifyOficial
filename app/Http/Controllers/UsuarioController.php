<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;
use App\Models\Cuenta;
use App\Models\Historial;

use Illuminate\Support\Facades\Auth;
class UsuarioController extends Controller
{
    public function __construct() {
        $this->middleware('can:usuarios')->only('index');
        $this->middleware('can:usuarios.change')->only('change');
        $this->middleware('can:usuarios.update')->only('update');
        $this->middleware('can:usuarios.destroy')->only('destroy');
    }
    public function index()
    {
        $usuarios = ViewUsuarioActivo::orderBy('fecha_vencimiento')->orderBy('nombre_cliente')->get();
        return view('inventory.usuarios.index', compact('usuarios'));
    }
    // Crear
    public function create()
    {
        //return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        //es una vista
    }

    // Editar un usuario existente
    public function change($iddet)
    {
        $usuario = ViewUsuarioActivo::where('iddet',$iddet)->first();
        //$detalle = DetalleVenta::where('iddet',$iddet)->first();
        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

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
        return view('inventory.usuarios.change', compact('usuario','cuentas'));
    }

    public function update(Request $request, $iddet)
    {
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue', // La cuenta debe existir en la tabla 'cuentas'
            'perfil' => 'required|integer|min:1', // Validación para un número entero
            'fecha_vencimiento' => 'required'
        ]);
        //$iddet = $request->iddet;
        $detalle = DetalleVenta::findOrFail($iddet);

        // Actualizar los campos del usuario
        $detalle->idper = $request->idcue.'.'.$request->perfil;
        $detalle->fechavendet = $request->fecha_vencimiento;
        Historial::create([
            'accion' => 'Actualización de Usuario',
            'descripcion' => 'Cliente: '.$detalle->venta->cliente->nombrecli. 'Datos antigüos: ' . json_encode($detalle), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        // Guardar los cambios
        $detalle->save();

        // Redirigir con un mensaje de éxito
        return redirect()->route('usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    // Eliminar un usuario
    public function destroy($iddet)
    {
        $detalle = DetalleVenta::findOrFail($iddet);
        // Cambiar el estado de activodet (de true a false o de false a true)
        $detalle->activodet = !$detalle->activodet; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $detalle->save();

        Historial::create([
            'accion' => 'Cuenta-Quitada',
            'descripcion' =>  'Cliente: '.$detalle->venta->cliente->nombrecli. 'Usuario que se quitó: ' . json_encode($detalle), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('usuarios')->with('success', 'Usuario eliminado con éxito.');;
    }
}
