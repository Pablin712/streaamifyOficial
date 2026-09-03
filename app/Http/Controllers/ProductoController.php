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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductosSriExport;

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

    /**
     * Ya no existe una vista `create` para productos: se crean desde un modal
     * dentro del listado. Esta ruta devolvia "View not found" (error 500), asi
     * que ahora redirige al listado con el modal ya abierto. Se conserva para
     * que enlaces antiguos y marcadores sigan funcionando.
     */
    public function create()
    {
        if (!Gate::allows('productos.store')) {
            abort(403, 'No tienes permiso para crear productos.');
        }

        return redirect()->route('productos.index', ['modal' => 'createProductoModal']);
    }

    public function store(Request $request)
    {
        // VERIFICACIÓN 1: Permisos
        if (!Gate::allows('productos.store')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para crear productos.'], 403);
            }
            abort(403, 'No tienes permiso para crear productos.');
        }

        // VERIFICACIÓN 2: Validación
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
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'El formato de los detalles del producto es inválido.'], 422);
            }
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

        // VERIFICACIÓN 3: Respuesta según tipo de petición
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto creado exitosamente.', 'producto' => $producto->load(['categoria', 'tipoProducto', 'detalles'])]);
        }

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function show(Request $request, string $id)
    {
        if (!Gate::allows('productos.show')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para ver el producto.'], 403);
            }
            abort(403, 'No tienes permiso para ver el producto.');
        }
        $producto = Producto::with(['categoria', 'tipoProducto', 'detalles'])->findOrFail($id);

        // Si es petición AJAX, devolver JSON
        if ($request->expectsJson()) {
            return response()->json($producto);
        }

        return view('inventory.productos.show', compact('producto'));
    }

    public function exportarSRI()
    {
        if (!Gate::allows('productos.index')) {
            abort(403, 'No tienes permiso para exportar productos.');
        }

        $path = (new ProductosSriExport())->generate();
        $filename = 'Productos_SRI_' . now()->format('Y-m-d') . '.xlsx';

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function curarCodigos(Request $request)
    {
        if (!Gate::allows('productos.update')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar productos.'], 403);
        }

        $productos = Producto::with('detalles')->get();
        $omitidos = 0;

        // Paso 1: calcular el código objetivo para cada producto
        $targetCodes = [];
        foreach ($productos as $producto) {
            $detalles = $producto->detalles;
            if ($detalles->isEmpty()) {
                $omitidos++;
                continue;
            }
            $uniqueServices = $detalles->pluck('idser')
                ->map(fn ($s) => strtoupper(trim($s)))->unique()->sort()->values();
            $serviceKey = $uniqueServices->implode('+');
            $codeHash = strtoupper(substr(md5($serviceKey), 0, 8));
            $monthCount = (int) $detalles->first()->meses;
            $uniqueServiceCount = $uniqueServices->count();
            $deviceCount = max(1, (int) floor($detalles->count() / $uniqueServiceCount));
            $codePrefix = $uniqueServiceCount > 1 ? 'C' : 'I';
            $targetCodes[$producto->id] = sprintf('%s%02dM%02dD%s', $codePrefix, $monthCount, $deviceCount, $codeHash);
        }

        // Paso 2: filtrar solo los que necesitan cambio
        $toUpdate = [];
        foreach ($productos as $producto) {
            if (isset($targetCodes[$producto->id]) && $producto->codigopro !== $targetCodes[$producto->id]) {
                $toUpdate[$producto->id] = $targetCodes[$producto->id];
            }
        }

        // Paso 3: detectar conflictos con productos que NO se van a actualizar
        $idsToUpdate = array_keys($toUpdate);
        $stableCodes = $productos->filter(fn ($p) => !in_array($p->id, $idsToUpdate))
            ->pluck('codigopro')->flip();

        $conflictos = [];
        foreach ($toUpdate as $id => $newCode) {
            if ($stableCodes->has($newCode)) {
                $conflictos[] = "ID {$id} → {$newCode} ya pertenece a otro producto";
                unset($toUpdate[$id]);
            }
        }

        // Paso 4: detectar duplicados internos (varios productos con el mismo código nuevo)
        $seenCodes = [];
        foreach ($toUpdate as $id => $newCode) {
            if (isset($seenCodes[$newCode])) {
                $conflictos[] = "ID {$id} → {$newCode} duplicado con ID {$seenCodes[$newCode]}";
                unset($toUpdate[$id]);
            } else {
                $seenCodes[$newCode] = $id;
            }
        }

        // Paso 5: actualizar en transacción usando códigos temporales primero
        // (evita violación de unicidad cuando el nuevo código de A coincide con el viejo de B)
        $actualizados = 0;
        DB::beginTransaction();
        try {
            foreach ($toUpdate as $id => $newCode) {
                DB::table('productos')->where('id', $id)->update(['codigopro' => 'TMP_' . $id]);
            }
            foreach ($toUpdate as $id => $newCode) {
                DB::table('productos')->where('id', $id)->update([
                    'codigopro' => $newCode,
                    'updated_at' => now(),
                ]);
                $actualizados++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }

        $conflictosCount = count($conflictos);
        Historial::create([
            'accion' => 'Curación de Códigos de Productos',
            'descripcion' => "Actualizados: {$actualizados}, omitidos (sin detalles): {$omitidos}, conflictos: {$conflictosCount}",
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $mensaje = "Códigos curados. Actualizados: {$actualizados}";
        if ($omitidos > 0) $mensaje .= ", sin detalles omitidos: {$omitidos}";
        if ($conflictosCount > 0) $mensaje .= ", conflictos omitidos: {$conflictosCount}";

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'conflictos' => $conflictos,
        ]);
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
        // VERIFICACIÓN 1: Permisos
        if (!Gate::allows('productos.update')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar productos.'], 403);
            }
            abort(403, 'No tienes permiso para actualizar productos.');
        }

        // VERIFICACIÓN 2: Validación
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
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'El formato de los detalles del producto es inválido.'], 422);
            }
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

        // VERIFICACIÓN 3: Respuesta según tipo de petición
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto actualizado exitosamente.', 'producto' => $producto->load(['categoria', 'tipoProducto', 'detalles'])]);
        }

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
                $detalle = $detalles->first();
                if ($servicio == null) {
                    continue; // Si no hay servicio, pasar al siguiente detalle
                }
                if($detalle->meses > 1) {
                    $precio = $servicio->comboser * $detalle->meses + ($detalle->meses - 1) * 0.10;
                } else {
                    $precio = $servicio->precioser;
                }
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

    public function destroy(Request $request, string $id)
    {
        // VERIFICACIÓN 1: Permisos
        if (!Gate::allows('productos.destroy')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar productos.'], 403);
            }
            abort(403, 'No tienes permiso para eliminar productos.');
        }

        // VERIFICACIÓN 2: Validación - El producto existe
        $producto = Producto::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Producto',
            'descripcion' => 'Datos eliminados: ' . json_encode($producto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $producto->detalles()->delete();
        $producto->delete();

        // VERIFICACIÓN 3: Respuesta según tipo de petición
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Producto eliminado exitosamente.']);
        }

        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
