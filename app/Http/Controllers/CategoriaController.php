<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:gestion')->only('index');
        $this->middleware('can:categorias.store')->only('store');
        $this->middleware('can:categorias.update')->only('update');
        $this->middleware('can:categorias.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('gestion')) {
            abort(403, 'No tienes permiso para ver la gestión de productos.');
        }
        $categorias = Categoria::all();
        $tiposProducto = TipoProducto::all();
        return view('inventory.productos.gestion', compact('categorias','tiposProducto'));
    }

    public function create()
    {
        // Si en algún momento requieres protección en create, puedes agregar la verificación aquí.
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('categorias.store')) {
            abort(403, 'No tienes permiso para crear categorías.');
        }
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create($request->all());

        Historial::create([
            'accion' => 'Creación de categoría',
            'descripcion' => 'Datos: ' . json_encode($categoria),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        return redirect()->route('gestion.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function show(string $id)
    {
        // Implementa si es necesario
        $categoria = Categoria::findOrFail($id);
        return view('categorias.show', compact('categoria'));
    }

    public function edit(string $id)
    {
        if (!Auth::user()->hasPermissionTo('categorias.update')) {
            abort(403, 'No tienes permiso para editar categorías.');
        }
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::user()->hasPermissionTo('categorias.update')) {
            abort(403, 'No tienes permiso para actualizar categorías.');
        }
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);
        $categoria = Categoria::findOrFail($id);
        
        Historial::create([
            'accion' => 'Actualización de categoría',
            'descripcion' => 'Datos antiguos: ' . json_encode($categoria),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $categoria->update($request->all());
        return redirect()->route('gestion.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(string $id)
    {
        if (!Auth::user()->hasPermissionTo('categorias.destroy')) {
            abort(403, 'No tienes permiso para eliminar categorías.');
        }
        $categoria = Categoria::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de categoría',
            'descripcion' => 'Datos antiguos: ' . json_encode($categoria),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $categoria->delete();
        return redirect()->route('gestion.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}