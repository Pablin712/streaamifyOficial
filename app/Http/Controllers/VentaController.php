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
        $ventas = Venta::with(['empleado','cliente'])->orderBy('fechaven')->get(); // Cargar valor asociado
        // Inicializar una colección vacía para los detalles
        $detalles_venta = collect();

        $idvenSeleccionado = $request->idven;

        // Verificar si se seleccionó una cuenta para filtrar perfiles
        if ($idvenSeleccionado) {
            $detalles_venta = DetalleVenta::where('idven', $idvenSeleccionado)->get();
        }

        // Pasar las cuentas y los perfiles a la vista
        return view('sales.detalles.index', compact('ventas', 'detalles_venta', 'idvenSeleccionado'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::all(); // Obtener lista de clientes
        $empleados = Empleado::all(); //Obtener lista de empleados
        return view('sales.detalles.create', compact('empleados','clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idemp' => 'required|exists:empleados,idemp',
            'idcli' => 'required|exists:clientes,idcli'
        ]);

        //Cuenta::create($request->all());

        // Crear la cuenta (otra alternativa)
        $venta = Venta::create($validated);
        // Verificar si se recibieron datos de costo
        if ($request->has('descripcioncos') && $request->has('montocos')) {
            // Crear el costo asociado a la cuenta
            DetalleVenta::create([
                'idcue' => $venta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),  // O la fecha que desees
            ]);
        }
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
