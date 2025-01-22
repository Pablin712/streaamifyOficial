<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\TipoProducto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
class TipoProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all(); // Obtiene todas las categorías
        $tiposProducto = TipoProducto::all(); // Obtiene todos los tipos de producto
        return view('inventory.productos.gestion', compact('categorias','tiposProducto')); // Retorna una vista
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoProducto = TipoProducto::create($request->all());

        Historial::create([
            'accion' => 'Se creó el tipo de producto con ID: ' . $tipoProducto->id,
            'descripcion' => 'Datos: ' . json_encode($tipoProducto), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        return redirect()->route('gestion.index')->with('success', 'Tipo de producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tipoProducto = TipoProducto::findOrFail($id);
        return view('tipos_producto.show', compact('tipoProducto')); // Si usas modals, esta vista es opcional
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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoProducto = TipoProducto::findOrFail($id);
        $tipoProducto->update($request->all());

        Historial::create([
            'accion' => 'Se actualizó el tipo de producto con ID: ' . $tipoProducto->id,
            'descripcion' => 'Datos: ' . json_encode($tipoProducto), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('gestion.index')->with('success', 'Tipo de producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tipoProducto = TipoProducto::findOrFail($id);
        $tipoProducto->delete();

        Historial::create([
            'accion' => 'Se eliminó el tipo de producto con ID: ' . $tipoProducto->id,
            'descripcion' => 'Datos eliminados: ' . json_encode($tipoProducto), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('gestion.index')->with('success', 'Tipo de producto eliminado exitosamente.');
    }
}
