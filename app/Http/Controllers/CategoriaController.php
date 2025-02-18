<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
class CategoriaController extends Controller
{
    public function __construct() {
        $this->middleware('can:gestion')->only('index');
        $this->middleware('can:categorias.store')->only('store');
        $this->middleware('can:categorias.update')->only('update');
        $this->middleware('can:categorias.destroy')->only('destroy');
    }
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
        return view('categorias.create'); // Retorna una vista (que crearás después)
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

        $categoria = Categoria::create($request->all());

        Historial::create([
            'accion' => 'Creación de categoria',
            'descripcion' =>  'Datos: ' . json_encode($categoria), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        return redirect()->route('gestion.index')->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('categorias.show', compact('categoria')); // Retorna una vista (opcional)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('categorias.edit', compact('categoria')); // Retorna una vista (que crearás después)
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
        $categoria = Categoria::findOrFail($id);
        
        Historial::create([
            'accion' => 'Actualización de categoria',
            'descripcion' =>  'Datos antigüos: ' . json_encode($categoria), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. $request->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        $categoria->update($request->all());
        return redirect()->route('gestion.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de categoria',
            'descripcion' =>  'Datos antigüos: ' . json_encode($categoria), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        $categoria->delete();

        return redirect()->route('gestion.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
