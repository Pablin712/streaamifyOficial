<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use Illuminate\Http\Request;

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
        return view('sales.ventas.create', compact('empleados','clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // Guardar una nueva venta
    public function store(Request $request)
    {
        // Validar los datos de la venta
        $validated = $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'fechaven' => 'required|date'
        ]);

        // Crear la venta
        $venta = Venta::create($validated);

        return redirect()->route('ventas')->with('success', 'Venta creada con éxito.');
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
