<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\TipoProducto;
use App\Models\DetalleProducto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::with(['categoria', 'tipoProducto', 'detalles'])->get();
        $categorias = Categoria::all();
        $tiposProducto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.index', compact('productos', 'categorias', 'tiposProducto', 'servicios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        $tipos_producto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.create', compact('categorias', 'tipos_producto', 'servicios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigopro' => 'required|string|max:50|unique:productos,codigopro',
            'nombrepro' => 'required|string|max:255',
            'preciopro' => 'required|numeric|min:0',
            'estrellaspro' => 'nullable|numeric|min:0|max:5',
            'descripcionpro' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tipo_producto_id' => 'required|exists:tipos_producto,id',
            'categoria_id' => 'required|exists:categorias,id',
            'activo' => 'required|boolean',
            'detalles_producto' => 'required|json',
        ]);

        // Subir foto si existe
        $data = $request->all();
        // Subir la foto si existe
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension(); // Generar un nombre único
            $destinationPath = public_path('storage/fotos'); // Carpeta en public/storage/fotos
            $file->move($destinationPath, $filename); // Mover el archivo
            $data['foto'] = 'storage/fotos/' . $filename; // Ruta para guardar
        }

        // Crear producto
        $producto = Producto::create([
            'codigopro' => $request->codigopro,
            'nombrepro' => $request->nombrepro,
            'preciopro' => $request->preciopro,
            'estrellaspro' => $request->estrellaspro,
            'descripcionpro' => $request->descripcionpro,
            'foto' => $data['foto'],
            'tipo_producto_id' => $request->tipo_producto_id,
            'categoria_id' => $request->categoria_id,
            'activo' => $request->activo,
        ]);
        // Procesar los detalles
        $detalles = json_decode($request->detalles_producto, true);

        // Validar si el JSON se decodificó correctamente
        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->withErrors(['detalles_producto' => 'El formato de los detalles del producto es inválido.']);
        }

        foreach ($detalles as $detalle) {
            $producto->detalles()->create([
                'idser' => $detalle['idser'],
                'descripcion' => $detalle['descripcion'] ?? null,
                'meses' => $detalle['meses'] ?? null,
            ]);
        }

        Historial::create([
            'accion' => 'Creación de Producto',
            'descripcion' => 'Datos: ' . json_encode($producto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $producto = Producto::with(['categoria', 'tipoProducto', 'detalles'])->findOrFail($id);
        return view('inventory.productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $producto = Producto::with('detalles')->findOrFail($id);
        $categorias = Categoria::all();
        $tipos_producto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.edit', compact('producto', 'categorias', 'tipos_producto', 'servicios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'codigopro' => 'required|string|max:50|unique:productos,codigopro,' . $id,
            'nombrepro' => 'required|string|max:255',
            'preciopro' => 'required|numeric|min:0',
            'estrellaspro' => 'nullable|numeric|min:0|max:5',
            'descripcionpro' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tipo_producto_id' => 'required|exists:tipos_producto,id',
            'categoria_id' => 'required|exists:categorias,id',
            'activo' => 'required|boolean',
            'detalles' => 'nullable|array', // Validar array de detalles
            'detalles.*.idser' => 'required_with:detalles|integer',
            'detalles.*.descripcion' => 'nullable|string',
            'detalles.*.meses' => 'nullable|integer',
        ]);

        $producto = Producto::findOrFail($id);
        // Subir foto si existe
        $data = $request->all();
        // Subir la foto si existe
        if ($request->hasFile('foto')) {
            // Eliminar la foto anterior si existe
            if (!empty($producto->foto) && file_exists(public_path('storage/' . $producto->foto))) {
                unlink(public_path('storage/' . $producto->foto)); // Eliminar el archivo antiguo
            }
            $file = $request->file('foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension(); // Generar un nombre único
            $destinationPath = public_path('storage/fotos'); // Carpeta en public/storage/fotos
            $file->move($destinationPath, $filename); // Mover el archivo
            $data['foto'] = 'storage/fotos/' . $filename; // Ruta para guardar
        }

        $producto->update($request->except('foto', 'detalles'));

        // Procesar los detalles del producto
        $detalles = json_decode($request->detalles_producto, true);

        // Eliminar los detalles existentes y volver a crearlos
        $producto->detalles()->delete();

        foreach ($detalles as $detalle) {
            $producto->detalles()->create([
                'idser' => $detalle['idser'],
                'descripcion' => $detalle['descripcion'],
                'meses' => $detalle['meses'],
            ]);
        }

        Historial::create([
            'accion' => 'Actualización de Producto',
            'descripcion' => 'Datos: ' . json_encode($producto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $producto = Producto::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de Producto',
            'descripcion' => 'Datos eliminados: ' . json_encode($producto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);
        $producto->detalles()->delete(); // Eliminar detalles relacionados
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
