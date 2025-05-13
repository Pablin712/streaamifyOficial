<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\TipoProducto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TipoProductoController extends Controller
{
    /*
    public function __construct() {
        $this->middleware('can:tipos_producto.store')->only('store');
        $this->middleware('can:tipos_producto.update')->only('update');
        $this->middleware('can:tipos_producto.destroy')->only('destroy');
    }
    */
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('tipos_producto.store')) {
            abort(403, 'No tienes permiso para crear tipos de producto.');
        }
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoProducto = TipoProducto::create($request->all());

        Historial::create([
            'accion' => 'Creación de Tipo de producto',
            'descripcion' => 'Datos: ' . json_encode($tipoProducto), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Gate::allows('tipos_producto.update')) {
            abort(403, 'No tienes permiso para crear tipos de producto.');
        }
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoProducto = TipoProducto::findOrFail($id);
        $tipoProducto->update($request->all());

        Historial::create([
            'accion' => 'Actualización de Tipo de producto',
            'descripcion' => 'Datos: ' . json_encode($tipoProducto), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('gestion.index')->with('success', 'Tipo de producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Gate::allows('tipos_producto.destroy')) {
            abort(403, 'No tienes permiso para crear tipos de producto.');
        }
        $tipoProducto = TipoProducto::findOrFail($id);
        $tipoProducto->delete();

        Historial::create([
            'accion' => 'Eliminación de Tipo de producto',
            'descripcion' => 'Datos eliminados: ' . json_encode($tipoProducto), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('gestion.index')->with('success', 'Tipo de producto eliminado exitosamente.');
    }
}
