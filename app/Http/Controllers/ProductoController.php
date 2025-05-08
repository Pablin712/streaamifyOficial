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
use PDF;
use Illuminate\Support\Facades\Gate;

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
        if (!Gate::allows('productos.index')) {
            abort(403, 'No tienes permiso para ver los productos.');
        }
        $productos = Producto::with(['categoria', 'tipoProducto', 'detalles'])->get();
        $categorias = Categoria::all();
        $tiposProducto = TipoProducto::all();
        // Obtener los datos de servicios y serviciosConfig
        $servicios = Servicio::all();
        $serviciosConfig = $this->obtenerServiciosConConfig($servicios);

        return view('inventory.productos.index', compact('productos', 'categorias', 'tiposProducto', 'servicios', 'serviciosConfig'));
    }

    protected function obtenerServiciosConConfig($servicios)
    {
        // Configuración de los servicios importantes
        $serviciosConfig = [
            'NETFLIX' => ['color' => 'danger', 'icon' => 'logo_netflix.png', 'nombre' => 'Netflix'],
            'DISNEYP' => ['color' => 'primary', 'icon' => 'espn.jpg', 'nombre' => 'Disney+ Premium'],
            'DISNEYS' => ['color' => 'primary', 'icon' => 'disneyP.jpg', 'nombre' => 'Disney+ Standard'],
            'MAX' => ['color' => 'info', 'icon' => 'max.jpg', 'nombre' => 'HBO Max'],
            'PRIME' => ['color' => 'success', 'icon' => 'fa-amazon', 'nombre' => 'Amazon Prime'],
            'PARAMOUNT' => ['color' => 'primary', 'icon' => 'paramount.jpg', 'nombre' => 'Paramount+'],
            'CRUNCHY' => ['color' => 'warning', 'icon' => 'crunchy.jpg', 'nombre' => 'Crunchyroll'],
            'SPOTIFY' => ['color' => 'success', 'icon' => 'fa-spotify', 'nombre' => 'Spotify'],
            'MAGIS' => ['color' => 'dark', 'icon' => 'magis.jpg', 'nombre' => 'Magis TV'],
        ];

        // Combinar los datos de serviciosConfig con los datos de la base de datos
        foreach ($servicios as $servicio) {
            if (isset($serviciosConfig[$servicio->idser])) {
                $serviciosConfig[$servicio->idser]['precioser'] = $servicio->precioser;
                $serviciosConfig[$servicio->idser]['comboser'] = $servicio->comboser;
            }
        }

        return $serviciosConfig;
    }

    public function create()
    {
        if (!Gate::allows('productos.store')) {
            abort(403, 'No tienes permiso para crear productos.');
        }
        $categorias = Categoria::all();
        $tipos_producto = TipoProducto::all();
        $servicios = Servicio::all();
        return view('inventory.productos.create', compact('categorias', 'tipos_producto', 'servicios'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('productos.store')) {
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function show(string $id)
    {
        if (!Gate::allows('productos.show')) {
            abort(403, 'No tienes permiso para ver el producto.');
        }
        $producto = Producto::with(['categoria', 'tipoProducto', 'detalles'])->findOrFail($id);
        return view('inventory.productos.show', compact('producto'));
    }

    public function generarPDF()
    {
        // Obtener los productos en el orden específico
        $productosInmediataIndividual = Producto::where('tipo_producto_id', 1) // Entrega Inmediata
            ->where('categoria_id', 1) // Categoría Individual
            ->get();

        $productosCombos = Producto::where('categoria_id', 2) // Categoría Combo
            ->where('tipo_producto_id', 1)
            ->get();

        $productosPedidos = Producto::where('tipo_producto_id', 2) // Pedido
            ->where('activo', true)
            ->get();

        $productosPersonalizados = Producto::where('tipo_producto_id', 3) // Personalizado
            ->where('activo', true)
            ->get();

        $productosCompletos = Producto::where('categoria_id', 3) // Categoría Completo
            ->where('activo', true)
            ->get();

        // Fusionar todas las colecciones en el orden deseado
        $productosOrdenados = $productosInmediataIndividual
            ->concat($productosCombos)
            ->concat($productosPedidos)
            ->concat($productosPersonalizados)
            ->concat($productosCompletos);
        // Generar el PDF usando una vista específica
        $pdf = PDF::loadView('inventory.productos.pdf', compact('productosOrdenados'));

        // Descargar el PDF con un nombre específico
        return $pdf->download('Catalogo_Streamify.pdf');
    }

    public function edit(string $id)
    {
        if (!Gate::allows('productos.update')) {
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
        if (!Gate::allows('productos.update')) {
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
            $producto->foto = $data['foto'];
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function updatePrecios(Request $request)
    {
        if (!Gate::allows('productos.update')) {
            abort(403, 'No tienes permiso para actualizar productos.');
        }
        //validar los precios
        $request->validate([
            'precios' => 'required|array',
            'precios.*.precio' => 'required|numeric|min:0.75',
            'precios.*.combo' => 'required|numeric|min:0.5',
        ]);
        // redirectar a la ruta de productos.index si la validación falla
        $precios = $request->input('precios');
        // Actualizar los precios de los servicios
        foreach ($precios as $idser => $precio) {
            Servicio::where('idser', $idser)->update([
                'precioser' => $precio['precio'],
                'comboser' => $precio['combo'],
            ]);
        }

        // Recalcular y actualizar los precios de los productos
        $productos = Producto::with('detalles.servicio')->get();

        foreach ($productos as $producto) {
            $detalles = $producto->detalles;

            // Si no hay detalles, pasar al siguiente producto
            if ($detalles->isEmpty()) {
                continue;
            }

            if ($detalles->count() === 1) {
                // Si producto no es categoria individual, pasar al siguiente producto
                if ($producto->categoria_id != 1) {
                    continue;
                }
                // Un solo detalle
                $servicio = $detalles->first()->servicio;
                if ($servicio == null) {
                    continue; // Si no hay servicio, pasar al siguiente detalle
                }
                $precio = $servicio->precioser;
            } else {
                
                // Múltiples detalles: suma de precios
                $precio = $detalles->sum(function ($detalle) {
                    return $detalle->servicio ? $detalle->servicio->precioser : 0;
                });
                // Si algún detalle no tiene un servicio válido, pasar al siguiente producto
                if ($precio === 0) {
                    continue;
                }
            }
            // Verificar si es entero (decimales .00), y restar 0.01 si es así
            if (fmod($precio, 1) == 0.0) {
                $precio -= 0.01;
            }
            $producto->preciopro = round($precio, 2);
            // Guardar el precio actualizado del producto
            $producto->save();
        }
        // Crear un historial de actualización de precios
        Historial::create([
            'accion' => 'Actualización de Precios de Productos',
            'descripcion' => 'Precios actualizados: ' . json_encode($precios),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('productos.index')->with('success', 'Precios actualizados correctamente.');
    }

    public function destroy(string $id)
    {
        if (!Gate::allows('productos.destroy')) {
            abort(403, 'No tienes permiso para eliminar productos.');
        }
        $producto = Producto::findOrFail($id);
        Historial::create([
            'accion' => 'Eliminación de Producto',
            'descripcion' => 'Datos eliminados: ' . json_encode($producto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $producto->detalles()->delete();
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
