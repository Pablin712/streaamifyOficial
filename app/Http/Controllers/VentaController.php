<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Cuenta;
use App\Models\Perfil;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Carbon\Carbon;
class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todas las ventas con los detalles de cada una
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven')->get();
        $detalles_venta = collect();

        $idvenSeleccionada = $request->idven;

        if ($idvenSeleccionada) {
            // Obtener los detalles de venta asociados a una venta específica
            $detalles_venta = DetalleVenta::where('idven', $idvenSeleccionada)->get();
        }

        // Pasar las ventas y los detalles de venta a la vista
        return view('sales.ventas.index', compact('ventas', 'detalles_venta', 'idvenSeleccionada'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::all(); // Obtener lista de clientes
        $empleados = Empleado::all(); //Obtener lista de empleados
        // Obtener las cuentas con sus perfiles
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
        return view('sales.ventas.create', compact('empleados', 'clientes', 'cuentas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // Guardar una nueva venta
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        // Crear la venta
        $venta = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => Carbon::now(),
            'totalpagoven' => 0,  // Puedes calcular el total si lo deseas
        ]);

        // Decodificar los detalles de venta desde el JSON
        $detalles = json_decode($request->detalles_venta, true);

        // Registrar los detalles de venta
        foreach ($detalles as $detalle) {
            // Obtener el idcue de la cuenta
            $idcue = $detalle['cuenta'];

            // Aquí dividimos el perfil para obtener el número del perfil
            $numeroper = $detalle['perfil']; // Asumiendo que 'perfil' es algo como '1.5'

            // Concatenar el idcue y el numeroper para obtener el idperfil
            $idper = $idcue . '.' . $numeroper;
            // Guardar cada detalle en la tabla detalles_venta
            DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);
        }

        // Puedes calcular el total de la venta aquí y actualizarlo
        //$venta->total_venta = collect($detalles)->sum('monto');
        //$venta->save();

        // Redirigir a una página de éxito o mostrar un mensaje
        return redirect()->route('ventas')->with('success', 'Venta registrada correctamente');
    }

    public function storeCliente(Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:15|unique:clientes,telefonocli'
        ]);

        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();

        // Si el cliente ya existe, redirigir con mensaje de error
        if ($clienteExistente) {
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        }

        $cliente = Cliente::create($request->all());

        // Retornar el cliente recién creado como respuesta
        //return response()->json(['cliente' => $cliente]);
        return redirect()->route('ventas.create')->with('success', 'Cliente creado con éxito.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($idven)
    {
        $venta = Venta::with(['detalles_venta'])->findOrFail($idven);
        $clientes = Cliente::all();
        $empleados = Empleado::all();
        return view('sales.ventas.edit', compact('venta', 'clientes', 'empleados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idven)
    {
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'fechaven' => 'required|date',
            'caidaventa' => 'required|boolean',
        ]);

        $venta = Venta::findOrFail($idven);
        $venta->update($request->all());

        return redirect()->route('ventas')->with('success', 'Venta actualizada con éxito.');
    }

    public function status($iddet)
    {
        $detalle = DetalleVenta::findOrFail($iddet);
        // Cambiar el estado de activodet (de true a false o de false a true)
        $detalle->activodet = !$detalle->activodet; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $detalle->save();

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('ventas')->with('success', 'Estado de la cuenta del cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($idven)
    {
        $venta = Venta::findOrFail($idven);
        $venta->delete();

        return redirect()->route('ventas')->with('success', 'Venta eliminada con éxito.');
    }
}
