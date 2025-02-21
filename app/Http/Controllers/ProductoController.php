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
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:productos')->only('index');
        $this->middleware('can:productos.store')->only('create', 'store');
        $this->middleware('can:productos.show')->only('show');
        $this->middleware('can:productos.update')->only('edit', 'update');
        $this->middleware('can:productos.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('productos.index')) {
            abort(403, 'No tienes permiso para ver los productos.');
        }
        $productos = Producto::with(['categoria', 'tipoProducto', 'detalles'])->get();
        $categorias = Categoria::all();
        $tiposProducto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.index', compact('productos', 'categorias', 'tiposProducto', 'servicios'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('productos.store')) {
            abort(403, 'No tienes permiso para crear productos.');
        }
        $categorias = Categoria::all();
        $tipos_producto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.create', compact('categorias', 'tipos_producto', 'servicios'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('productos.store')) {
            abort(403, 'No tienes permiso para crear productos.');
        }
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

        $data = $request->all();

        // Subir la foto si existe
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/fotos');
            $file->move($destinationPath, $filename);
            $data['foto'] = 'storage/fotos/' . $filename;
        }

        $producto = Producto::create([
            'codigopro' => $request->codigopro,
            'nombrepro' => $request->nombrepro,
            'preciopro' => $request->preciopro,
            'estrellaspro' => $request->estrellaspro,
            'descripcionpro' => $request->descripcionpro,
            'foto' => $data['foto'] ?? null,
            'tipo_producto_id' => $request->tipo_producto_id,
            'categoria_id' => $request->categoria_id,
            'activo' => $request->activo,
        ]);

        // Procesar los detalles
        $detalles = json_decode($request->detalles_producto, true);
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

    public function show(string $id)
    {
        if (!Auth::user()->hasPermissionTo('productos.show')) {
            abort(403, 'No tienes permiso para ver el producto.');
        }
        $producto = Producto::with(['categoria', 'tipoProducto', 'detalles'])->findOrFail($id);
        return view('inventory.productos.show', compact('producto'));
    }

    public function edit(string $id)
    {
        if (!Auth::user()->hasPermissionTo('productos.update')) {
            abort(403, 'No tienes permiso para editar productos.');
        }
        $producto = Producto::with('detalles')->findOrFail($id);
        $categorias = Categoria::all();
        $tipos_producto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.edit', compact('producto', 'categorias', 'tipos_producto', 'servicios'));
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::user()->hasPermissionTo('productos.update')) {
            abort(403, 'No tienes permiso para actualizar productos.');
        }
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
            'detalles_producto' => 'required|json',
        ]);

        $producto = Producto::findOrFail($id);
        $data = $request->all();

        // Subir foto si existe
        if ($request->hasFile('foto')) {
            if (!empty($producto->foto) && file_exists(public_path($producto->foto))) {
                unlink(public_path($producto->foto));
            }
            $file = $request->file('foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/fotos');
            $file->move($destinationPath, $filename);
            $data['foto'] = 'storage/fotos/' . $filename;
        }

        $producto->update($request->except('foto', 'detalles_producto'));

        $detalles = json_decode($request->detalles_producto, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->withErrors(['detalles_producto' => 'El formato de los detalles del producto es inválido.']);
        }
        // Eliminar detalles existentes y recrearlos
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

    public function destroy(string $id)
    {
        if (!Auth::user()->hasPermissionTo('productos.destroy')) {
            abort(403, 'No tienes permiso para eliminar productos.');
        }
        $producto = Producto::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de Producto',
            'descripcion' => 'Datos eliminados: ' . json_encode($producto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);
        $producto->detalles()->delete();
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}