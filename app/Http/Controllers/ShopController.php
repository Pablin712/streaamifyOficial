<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\Auth;
use App\Models\Venta;
use App\Models\Perfil;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\ViewUsuarioActivo;
use App\Models\Historial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\facturaMail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Notifications\PedidosPendientes;
use App\Notifications\ComprasRealizadas;
use Illuminate\Support\Facades\Notification;

class ShopController extends Controller
{
    public function index()
    {
        $productosInmediataIndividual = Producto::where('tipo_producto_id', 1) // Entrega Inmediata
            ->where('categoria_id', 1) // Categoría Individual
            ->get();

        $productosCombos = Producto::where('categoria_id', 2) // Categoría Combo
            ->where('tipo_producto_id', 1)
            ->get();

        $productosPedidos = Producto::where('tipo_producto_id', 2) // Pedido
            ->whereNotIn('categoria_id', [3, 4])
            ->where('activo', true)
            ->get();

        $productosPersonalizados = Producto::where('tipo_producto_id', 3) // Personalizado
            ->where('activo', true)
            ->get();

        $productosCompletos = Producto::where('categoria_id', 3) // Categoría Completo
            ->where('activo', true)
            ->get();

        $juegos = Producto::where('categoria_id', 4) // Juegos
            ->where('activo', true)
            ->get();

        // Recuperar el carrito desde la sesión
        $cart = session()->get('cart', []);

        return view('shopping.index', compact(
            'productosInmediataIndividual',
            'productosCombos',
            'productosPedidos',
            'productosPersonalizados',
            'productosCompletos',
            'juegos',
            'cart'
        ));
    }
    // Añadir un producto al carrito
    public function addToCart(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        if (!$producto->activo || (int) $producto->tipo_producto_id !== 1 || $producto->detalles()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto no está disponible para compra inmediata.',
            ], 422);
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['cantidad']++;
        } else {
            $cart[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombrepro,
                'precio' => $producto->preciopro,
                'foto' => $producto->foto,
                'cantidad' => 1
            ];
        }

        Session::put('cart', $cart);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'message' => 'Producto añadido al carrito'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Producto añadido al carrito',
            'cart' => $cart
        ]);
    }

    // Ver el carrito
    public function viewCart()
    {
        $cart = Session::get('cart', []);
        return view('cliente.carrito', compact('cart'));
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // Vaciar el carrito
    public function clearCart()
    {
        Session::forget('cart');
        return response()->json(['message' => 'Carrito vaciado']);
    }

    // Proceder al checkout (compra)
    public function checkout()
    {
        if (!Auth::guard('cliente')->check()) {
            Session::put('url.intended', route('cart.checkout'));
            return redirect()->route('cliente.login')->with('error', 'Inicia sesión para finalizar tu compra.');
        }

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop')->with('error', 'Tu carrito está vacío.');
        }

        $pricing = $this->calculateCartPricing($cart);
        if (!($pricing['success'] ?? false)) {
            return redirect()->route('shop')->with('error', $pricing['message'] ?? 'No se pudo calcular tu carrito.');
        }

        $items = $pricing['items'];
        $totalCarrito = (float) $pricing['total'];

        $idCliente = Auth::guard('cliente')->user()->idcli;
        // Buscar el cliente en la base de datos
        $usuario = Cliente::findOrFail($idCliente);
        // Verificar saldo
        if ($usuario->saldo < $totalCarrito) {
            return back()->with('error', 'Saldo insuficiente para realizar la compra.');
        }

        DB::beginTransaction();
        try {
            $empleadoLaravelId = Empleado::where('nombreemp', 'Laravel')->value('idemp') ?? 1;

            // Crear venta
            $venta = new Venta();
            $venta->idemp = $empleadoLaravelId;
            $venta->idcli = $usuario->idcli;
            $venta->fechaven = now();
            $venta->totalpagoven = $totalCarrito;
            $venta->save();
            // 🔹 Obtener el ID generado por el trigger
            $venta->idven = DB::selectOne("SELECT idven FROM ventas WHERE idcli = ? ORDER BY fechaven DESC LIMIT 1", [$venta->idcli])->idven;

            $mensajesServicios = []; // Inicialización correcta
            $preciofinal = 0;
            foreach ($items as $item) {
                /** @var \App\Models\Producto $producto */
                $producto = $item['producto'];
                $cantidad = (int) $item['cantidad'];
                $lineTotal = (float) $item['line_total'];
                $remainingLineTotal = $lineTotal;

                for ($i = 0; $i < $cantidad; $i++) {
                    $unitTotal = $i === $cantidad - 1 ? round($remainingLineTotal, 2) : round($lineTotal / $cantidad, 2);
                    $remainingLineTotal -= $unitTotal;

                    // Procesar cada detalle del producto
                    $detailCount = max($producto->detalles->count(), 1);
                    $remainingUnitTotal = $unitTotal;

                    foreach ($producto->detalles->values() as $detailIndex => $detalle) {
                        // Buscar cuenta disponible
                        $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                        if (!$cuenta) {
                            $producto->update(['activo' => false]);
                            throw new \Exception("No hay cuentas disponibles para el servicio.");
                        }

                        // Buscar perfil disponible
                        $perfil = $this->buscarPerfilDisponible($cuenta, true);

                        if (!$perfil) {
                            throw new \Exception("No hay perfiles disponibles en la cuenta seleccionada.");
                        }

                        $montoDetalle = $detailIndex === $detailCount - 1
                            ? round($remainingUnitTotal, 2)
                            : round($unitTotal / $detailCount, 2);
                        $remainingUnitTotal -= $montoDetalle;

                        // Registrar detalle de venta
                        $detalleVenta = new DetalleVenta();
                        $detalleVenta->idven = $venta->idven;
                        $detalleVenta->idper = $perfil->idper;
                        $detalleVenta->fechavendet = now()->addMonths($detalle->meses)->subDay();
                        $detalleVenta->descripciondet = "🤖 Venta automatizada";
                        $detalleVenta->montodet = $montoDetalle;
                        $detalleVenta->activodet = true;
                        $detalleVenta->save();
                        $preciofinal += $detalleVenta->montodet;
                        // Enviar mensaje de entrega
                        // Generar mensaje de servicio adquirido
                        $mensaje = "**" . $perfil->cuenta->valor->servicio->nombreser . "**\n" .
                            "Usuario: " . $perfil->cuenta->usuariocue . "\n" .
                            "Clave: " . $perfil->cuenta->contrasenacue . "\n" .
                            "PIN perfil {$perfil->numeroper}: " . $perfil->pinper;

                        $mensajesServicios[] = $mensaje;
                    }
                }
            }

            // Descontar saldo al usuario
            $usuario->saldo -= $preciofinal;
            $usuario->save();

            // Verificar si el cliente ya compró
            if (!$usuario->ya_compro) {
                $usuario->ya_compro = true; // Marcar como que ya compró
                $usuario->save();

                // Si tiene un referidor, darle $1 de saldo
                if ($usuario->referido_por) {
                    $referidor = Cliente::find($usuario->referido_por);
                    if ($referidor) {
                        $referidor->saldo += 1;
                        $referidor->save();

                        // Registrar en el historial
                        Historial::create([
                            'accion' => 'Bonificación por Referido',
                            'descripcion' => 'Se otorgó $1 al cliente ' . $referidor->nombrecli . ' por referir al cliente ' . $usuario->nombrecli,
                            'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            // Verificar si la cuenta se llenó
            $this->verificarCuentaLlena($cuenta, $producto);
            //dd($cuenta);
            // Guardar mensaje de éxito en sesión
            session()->flash('compra_exitosa', [
                'nombre' => 'Productos Comprados',
                'precio' => $preciofinal,
                'subtotal' => (float) $pricing['subtotal'],
                'descuento' => (float) $pricing['discount'],
                'servicios' => $mensajesServicios // Aquí guardamos los servicios adquiridos
            ]);

            DB::commit();
            Session::forget('cart');

            // Lógica para generar y enviar la factura por correo
            $this->sendInvoiceIfPossible($venta);
            //Notificar a empleado
            $empleados = Empleado::all();
            Notification::send($empleados, new ComprasRealizadas($venta));
            event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));

            return redirect()->route('shop');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    //probando comprar v1
    public function comprar(Request $request, $productoId)
    {
        $producto = Producto::with('detalles')->findOrFail($productoId);

        if (!$producto->activo) {
            return back()->with('error', 'Este producto no está disponible en este momento.');
        }

        // Obtener el ID del cliente autenticado
        $idCliente = Auth::guard('cliente')->user()->idcli;
        // Buscar el cliente en la base de datos
        $usuario = Cliente::findOrFail($idCliente);
        // Verificar saldo
        if ($usuario->saldo < $producto->preciopro) {
            return back()->with('error', 'Saldo insuficiente para realizar la compra.');
        }

        // Si el producto es de tipo inmediata (id=1)
        if ($producto->tipo_producto_id == 1) {
            // Verificar disponibilidad antes de la transacción
            foreach ($producto->detalles as $detalle) {
                if (!$this->buscarCuentaDisponible($detalle->idser)) {
                    // No hay cuentas disponibles: Desactivar el producto
                    $producto->update(['activo' => false]);
                    return back()->with('error', 'No hay cuentas disponibles para este servicio.');
                }
            }
            DB::beginTransaction();
            try {
                $mensajesServicios = [];

                // Crear venta
                $fecha = now();
                $venta = new Venta();
                $venta->idemp = Empleado::where('nombreemp', 'Laravel')->value('idemp');
                $venta->idcli = $usuario->idcli;
                $venta->fechaven = $fecha;
                $venta->totalpagoven = $producto->preciopro;
                $venta->save(); // Laravel asigna automáticamente el idven
                // Obtener el ID generado por el trigger
                $venta->idven = DB::selectOne("SELECT idven FROM ventas WHERE idcli = ? ORDER BY fechaven DESC LIMIT 1", [$venta->idcli])->idven;
                // Verificar si el ID de la venta es válido
                if (!$venta->idven) {
                    throw new \Exception("Error al crear la venta, ID de venta no encontrado.");
                }


                // Procesar cada detalle del producto
                foreach ($producto->detalles as $detalle) {
                    // Buscar cuenta disponible
                    $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                    if (!$cuenta) {
                        $producto->update(['activo' => false]);
                        Historial::create([
                            'accion' => 'Sin-Stock',
                            'descripcion' =>  'Ya no hay cuentas disponibles para: ' . json_encode($producto), // Campo opcional
                            'realizado_por' => $usuario->nombrecli . ' | ' . request()->ip(), // Almacena el nombre del usuario
                            'fecha' => now(),
                        ]);
                        throw new \Exception("No hay cuentas disponibles para el servicio.");
                    }

                    // Buscar perfil disponible
                    $perfil = $this->buscarPerfilDisponible($cuenta, true);

                    if (!$perfil) {
                        throw new \Exception("No hay perfiles disponibles en la cuenta seleccionada.");
                    }

                    // Registrar detalle de venta
                    $detalleVenta = new DetalleVenta();
                    $detalleVenta->idven = $venta->idven;
                    $detalleVenta->idper = $perfil->idper;
                    $detalleVenta->fechavendet = now()->addMonths($detalle->meses)->subDay();
                    $detalleVenta->descripciondet = "🤖 Venta automatizada";
                    $detalleVenta->montodet = $producto->preciopro / count($producto->detalles);
                    $detalleVenta->activodet = true;
                    $detalleVenta->save();

                    // Enviar mensaje de entrega
                    // Generar mensaje de servicio adquirido
                    $mensaje = "**" . $perfil->cuenta->valor->servicio->nombreser . "**\n" .
                        "Usuario: " . $perfil->cuenta->usuariocue . "\n" .
                        "Clave: " . $perfil->cuenta->contrasenacue . "\n" .
                        "PIN perfil {$perfil->numeroper}: " . $perfil->pinper;

                    $mensajesServicios[] = $mensaje;
                }
                Historial::create([
                    'accion' => 'Compra-Automática',
                    'descripcion' =>  'Cliente realizó una compra en el Eccomerce: ' . json_encode($venta), // Campo opcional
                    'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
                    'created_at' => now(),
                ]);
                // Verificar si el cliente ya compró
                if (!$usuario->ya_compro) {
                    $usuario->ya_compro = true; // Marcar como que ya compró
                    $usuario->save();

                    // Si tiene un referidor, darle $1 de saldo
                    if ($usuario->referido_por) {
                        $referidor = Cliente::find($usuario->referido_por);
                        if ($referidor) {
                            $referidor->saldo += 1;
                            $referidor->save();

                            // Registrar en el historial
                            Historial::create([
                                'accion' => 'Bonificación por Referido',
                                'descripcion' => 'Se otorgó $1 al cliente ' . $referidor->nombrecli . ' por referir al cliente ' . $usuario->nombrecli,
                                'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
                // Descontar saldo al usuario
                $usuario->saldo -= $producto->preciopro;
                $usuario->save();

                // Verificar si la cuenta se llenó
                $this->verificarCuentaLlena($cuenta, $producto);
                //dd($cuenta);
                // Guardar mensaje de éxito en sesión
                session()->flash('compra_exitosa', [
                    'nombre' => $producto->nombrepro,
                    'precio' => $producto->preciopro,
                    'subtotal' => (float) $producto->preciopro,
                    'descuento' => 0,
                    'servicios' => $mensajesServicios // Aquí guardamos los servicios adquiridos
                ]);

                DB::commit();

                // Lógica para generar y enviar la factura por correo
                $this->sendInvoiceIfPossible($venta);
                $empleados = Empleado::all();
                Notification::send($empleados, new ComprasRealizadas($venta));
                event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));

                return redirect()->route('shop');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        } else {
            // Verificamos si el producto es de tipo "Personalizado"
            if ($producto->tipo_producto_id == 3) {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required|min:6',
                ]);

                // Guardamos el pedido incluyendo el correo y la contraseña en 'respuesta'
                $pedido = Pedido::create([
                    'idcli' => $usuario->idcli,
                    'producto_id' => $producto->id,
                    'fechapedido' => now(),
                    'respuesta' => 'Quiero en mi cuenta con los datos-> Correo: ' . $request->email . ' | Contraseña: ' . $request->password,
                ]);
            } else {
                // Pedido normal (sin credenciales)
                $pedido = Pedido::create([
                    'idcli' => $usuario->idcli,
                    'producto_id' => $producto->id,
                    'fechapedido' => now(),
                    'respuesta' => 'Sin responder',
                ]);
            }
            //Notificar a empleado
            $empleados = Empleado::all();
            Notification::send($empleados, new PedidosPendientes($pedido));
            event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));
            // Mensaje de sesión para mostrar en la vista
            session()->flash('pedido_registrado', [
                'nombre' => $producto->nombrepro,
                'precio' => $producto->preciopro
            ]);
            return redirect()->route('shop');
        }
    }

    public function renovar(Request $request, $id)
    {
        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*' => 'integer',
            'meses' => 'required|integer|min:1|max:12',
        ]);

        $idCliente = Auth::guard('cliente')->user()->idcli;
        $venta = Venta::where('idven', $id)
            ->where('idcli', $idCliente)
            ->firstOrFail();


        // Perfiles seleccionados por el usuario para renovar
        $detallesSeleccionados = array_map('intval', $request->input('detalles', []));

        $detallesValidos = DetalleVenta::where('idven', $venta->idven)
            ->where('activodet', true)
            ->whereIn('iddet', $detallesSeleccionados)
            ->pluck('iddet')
            ->map(fn ($value) => (int) $value)
            ->toArray();

        if (empty($detallesValidos)) {
            return redirect()->back()->with('error', 'Los perfiles seleccionados no pertenecen a una suscripción activa.');
        }

        $meses = (int) $request->input('meses');

        $detallesVenta = DetalleVenta::with(['perfil.cuenta.valor.servicio'])
            ->where('idven', $venta->idven)
            ->where('activodet', true)
            ->whereIn('iddet', $detallesValidos)
            ->get();

        if ($detallesVenta->isEmpty()) {
            return redirect()->back()->with('error', 'No hay detalles válidos para renovar.');
        }

        $precioRenovacion = $this->calcularPrecioRenovacion($detallesVenta, $meses);

        if (($precioRenovacion['total'] ?? 0) <= 0) {
            return redirect()->back()->with('error', 'No se pudo calcular el monto de renovación.');
        }

        return $this->storeRenew(
            idCliente: $idCliente,
            ventaPasada: $venta,
            detallesSeleccionados: $detallesValidos,
            precioRenovacion: $precioRenovacion,
            meses: $meses,
        );
    }

    public function renovarPreview(Request $request, $id)
    {
        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*' => 'integer',
            'meses' => 'required|integer|min:1|max:12',
        ]);

        $idCliente = Auth::guard('cliente')->user()->idcli;
        $venta = Venta::where('idven', $id)
            ->where('idcli', $idCliente)
            ->firstOrFail();

        $detallesSeleccionados = array_map('intval', $request->input('detalles', []));
        $meses = (int) $request->input('meses');

        $detallesVenta = DetalleVenta::with(['perfil.cuenta.valor.servicio'])
            ->where('idven', $venta->idven)
            ->where('activodet', true)
            ->whereIn('iddet', $detallesSeleccionados)
            ->get();

        if ($detallesVenta->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Selecciona al menos un perfil válido para calcular el precio.',
            ], 422);
        }

        $precioRenovacion = $this->calcularPrecioRenovacion($detallesVenta, $meses);

        return response()->json([
            'success' => true,
            'data' => [
                'meses' => $meses,
                'total' => round((float) ($precioRenovacion['total'] ?? 0), 2),
                'precio_base_mensual' => round((float) ($precioRenovacion['base_mensual'] ?? 0), 2),
                'estrategia' => $precioRenovacion['pricing_strategy'] ?? 'normal',
                'descripcion' => $precioRenovacion['pricing_description'] ?? null,
                'producto_aplicado' => $precioRenovacion['combo_producto'] ?? null,
                'detalles' => $precioRenovacion['detalles'] ?? [],
            ],
        ]);
    }

    private function buscarProductoPorDetalles($detallesSeleccionados)
    {
        // Obtener los idser (servicios) de los detalles seleccionados
        $idserVenta = DetalleVenta::whereIn('iddet', $detallesSeleccionados)
            ->get()
            ->map(function ($detalle) {
                return $detalle->perfil->cuenta->valor->servicio->idser;
            })
            ->unique();

        // Buscar productos cuyos detalles coincidan con los servicios seleccionados
        $productos = Producto::whereHas('detalles', function ($query) use ($idserVenta) {
            $query->whereIn('idser', $idserVenta);
        })->get();

        // Filtrar productos donde TODOS los idser de sus detalles coincidan con los seleccionados
        foreach ($productos as $producto) {
            $idserProducto = $producto->detalles->pluck('idser')->unique();
            if ($idserProducto->sort()->values()->toArray() === $idserVenta->sort()->values()->toArray()) {
                return $producto;
            }
        }

        return null;
    }

    private function storeRenew($idCliente, $ventaPasada, array $detallesSeleccionados, array $precioRenovacion, int $meses)
    {
        DB::beginTransaction();

        try {
            $detallesVenta = DetalleVenta::where('idven', $ventaPasada->idven)
                ->where('activodet', true)
                ->whereIn('iddet', $detallesSeleccionados)
                ->lockForUpdate()
                ->get();

            if ($detallesVenta->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No hay detalles de venta disponibles.');
            }

            $usuario = Cliente::where('idcli', $idCliente)->lockForUpdate()->firstOrFail();
            $totalRenovacion = (float) ($precioRenovacion['total'] ?? 0);

            if ($usuario->saldo < $totalRenovacion) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Saldo insuficiente para realizar la renovación.');
            }

            $fecha = Carbon::today()->toDateString();

            // Crear la nueva venta
            $ventaNueva = Venta::create([
                'idcli' => $idCliente,
                'idemp' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
                'fechaven' => $fecha,
                'totalpagoven' => $totalRenovacion,
            ]);
            // 🔹 Obtener el ID generado por el trigger
            $ventaNueva->idven = DB::selectOne("SELECT idven FROM ventas WHERE idcli = ? ORDER BY created_at DESC LIMIT 1", [$ventaNueva->idcli])->idven;

            // Desactivar los detalles anteriores solo para los seleccionados
            DetalleVenta::where('idven', $ventaPasada->idven)
                ->whereIn('iddet', $detallesSeleccionados)
                ->update(['activodet' => false]);

            $detallesRenovados = [];
            $montoPorDetalle = collect($precioRenovacion['detalles'] ?? [])->keyBy('iddet');

            foreach ($detallesVenta as $detalle) {
                $nuevaFechaVencimiento = Carbon::parse($detalle->fechavendet)->addMonths($meses);
                $montoDetalle = (float) ($montoPorDetalle->get($detalle->iddet)['monto'] ?? $detalle->montodet);

                DetalleVenta::create([
                    'idven' => $ventaNueva->idven,
                    'idper' => $detalle->idper,
                    'descripciondet' => '🤖 Renovación automatizada (' . $meses . ' mes(es))',
                    'fechavendet' => $nuevaFechaVencimiento,
                    'montodet' => $montoDetalle,
                    'activodet' => true,
                ]);

                $detallesRenovados[] = [
                    'iddet' => $detalle->iddet,
                    'servicio' => $detalle->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'Servicio',
                    'cuenta' => $detalle->perfil?->cuenta?->usuariocue ?? 'No disponible',
                    'perfil' => $detalle->perfil?->numeroper ?? 'N/A',
                    'fecha_anterior' => optional($detalle->fechavendet)?->format('d/m/Y') ?? 'N/A',
                    'fecha_nueva' => $nuevaFechaVencimiento->format('d/m/Y'),
                    'monto' => $montoDetalle,
                ];
            }

            // Registrar en historial
            Historial::create([
                'accion' => 'Renovación-Automática ' . $ventaPasada->idven,
                'descripcion' => 'Nueva venta creada: ' . json_encode($ventaNueva),
                'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
                'created_at' => now(),
            ]);

            // Descontar saldo al usuario con base en la selección real
            $usuario->saldo -= $totalRenovacion;
            $usuario->save();

            DB::commit();

            // Lógica para generar y enviar la factura por correo
            if (!empty($ventaNueva->cliente?->email)) {
                Mail::to($ventaNueva->cliente->email)->send(new facturaMail($ventaNueva));
            }

            $empleados = Empleado::all();
            Notification::send($empleados, new ComprasRealizadas($ventaNueva));
            event(new \Illuminate\Support\Facades\Event('notificacionRecibida'));

            $primerDetalle = $ventaNueva->detalles_venta->first();

            return redirect()->back()->with('renovacion_exitosa', [
                'nombre' => $primerDetalle?->perfil?->cuenta?->valor?->servicio?->nombreser ?? 'Servicio',
                'fecha_vencimiento' => optional($primerDetalle?->fechavendet)?->format('d/m/Y') ?? Carbon::now()->format('d/m/Y'),
                'meses' => $meses,
                'total_descontado' => round($totalRenovacion, 2),
                'saldo_actual' => round((float) $usuario->saldo, 2),
                'pricing_strategy' => $precioRenovacion['pricing_strategy'] ?? 'normal',
                'pricing_description' => $precioRenovacion['pricing_description'] ?? null,
                'combo_producto' => $precioRenovacion['combo_producto'] ?? null,
                'detalles' => $detallesRenovados,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'No se pudo completar la renovación. Intenta nuevamente.');
        }
    }

    private function calcularPrecioRenovacion(Collection $detallesVenta, int $meses): array
    {
        $baseMensual = (float) $detallesVenta->sum('montodet');
        $servicios = $detallesVenta
            ->map(fn ($detalle) => (string) ($detalle->perfil?->cuenta?->valor?->servicio?->idser ?? ''))
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        $productoCombo = $this->buscarProductoExactoParaRenovacion($servicios, $meses);

        if ($productoCombo) {
            $total = (float) $productoCombo->preciopro;
            $detallesProrrateados = $this->prorratearMontoPorDetalles($detallesVenta, $total);

            return [
                'total' => round($total, 2),
                'base_mensual' => round($baseMensual, 2),
                'pricing_strategy' => 'combo_producto',
                'pricing_description' => 'Se aplicó el precio de combo configurado para ' . $meses . ' mes(es).',
                'combo_producto' => [
                    'id' => $productoCombo->id,
                    'nombre' => $productoCombo->nombrepro,
                    'precio' => round($total, 2),
                    'meses' => $meses,
                ],
                'detalles' => $detallesProrrateados,
            ];
        }

        if ($meses === 1) {
            $detallesNormales = $detallesVenta->map(function ($detalle) {
                return [
                    'iddet' => (int) $detalle->iddet,
                    'monto' => round((float) $detalle->montodet, 2),
                ];
            })->values()->toArray();

            return [
                'total' => round($baseMensual, 2),
                'base_mensual' => round($baseMensual, 2),
                'pricing_strategy' => 'precio_normal',
                'pricing_description' => 'Se aplicó el precio mensual normal del detalle.',
                'combo_producto' => null,
                'detalles' => $detallesNormales,
            ];
        }

        $descuentoModerado = 0.10;
        $subtotal = $baseMensual * $meses;
        $totalConDescuento = round($subtotal * (1 - $descuentoModerado), 2);

        return [
            'total' => $totalConDescuento,
            'base_mensual' => round($baseMensual, 2),
            'pricing_strategy' => 'mayoreo_descuento',
            'pricing_description' => 'Se aplicó descuento moderado del 10% por renovación de ' . $meses . ' meses.',
            'combo_producto' => null,
            'detalles' => $this->prorratearMontoPorDetalles($detallesVenta, $totalConDescuento),
        ];
    }

    private function buscarProductoExactoParaRenovacion(array $serviciosOrdenados, int $meses): ?Producto
    {
        if (empty($serviciosOrdenados)) {
            return null;
        }

        $serviciosUnicos = array_values(array_unique($serviciosOrdenados));
        $cantidadServicios = count($serviciosOrdenados);

        $productos = Producto::with('detalles')
            ->where('activo', true)
            ->whereHas('detalles', function ($query) use ($serviciosUnicos, $meses) {
                $query->whereIn('idser', $serviciosUnicos)
                    ->where('meses', $meses);
            })
            ->get();

        foreach ($productos as $producto) {
            $serviciosProducto = $producto->detalles
                ->where('meses', $meses)
                ->pluck('idser')
                ->map(fn ($idser) => (string) $idser)
                ->sort()
                ->values()
                ->toArray();

            if (count($serviciosProducto) !== $cantidadServicios) {
                continue;
            }

            if ($serviciosProducto === $serviciosOrdenados) {
                return $producto;
            }
        }

        return null;
    }

    private function prorratearMontoPorDetalles(Collection $detallesVenta, float $total): array
    {
        $baseMensual = (float) $detallesVenta->sum('montodet');
        $detalles = $detallesVenta->values();

        if ($detalles->isEmpty()) {
            return [];
        }

        if ($baseMensual <= 0) {
            $montoUniforme = round($total / $detalles->count(), 2);
            $acumulado = 0;

            return $detalles->map(function ($detalle, $index) use ($detalles, $montoUniforme, $total, &$acumulado) {
                $monto = $index === $detalles->count() - 1 ? round($total - $acumulado, 2) : $montoUniforme;
                $acumulado += $monto;

                return [
                    'iddet' => (int) $detalle->iddet,
                    'monto' => $monto,
                ];
            })->values()->toArray();
        }

        $acumulado = 0;

        return $detalles->map(function ($detalle, $index) use ($detalles, $baseMensual, $total, &$acumulado) {
            $proporcion = ((float) $detalle->montodet) / $baseMensual;
            $monto = $index === $detalles->count() - 1
                ? round($total - $acumulado, 2)
                : round($total * $proporcion, 2);

            $acumulado += $monto;

            return [
                'iddet' => (int) $detalle->iddet,
                'monto' => $monto,
            ];
        })->values()->toArray();
    }

    private function buscarCuentaDisponible($idser)
    {
        return Cuenta::whereHas('valor', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })
            ->where('caidacue', false)
            ->where('activocue', true)
            ->whereHas('valor', function ($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = cuentas.idcue) < valores.pantmaxval');
            })
            ->first();
    }

    private function buscarPerfilDisponible($cuenta, bool $forUpdate = false)
    {
        $query = Perfil::where('idcue', $cuenta->idcue)->orderBy('numeroper');

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $perfiles = $query->get();

        foreach ([0, 1] as $ocupacionPermitida) {
            foreach ($perfiles as $perfil) {
                $usuariosActivos = DetalleVenta::where('idper', $perfil->idper)
                    ->where('activodet', true)
                    ->whereDate('fechavendet', '>=', now()->toDateString())
                    ->count();

                if ($usuariosActivos === $ocupacionPermitida) {
                    return $perfil;
                }
            }
        }

        return null;
    }

    private function calculateCartPricing(array $cart): array
    {
        $items = [];
        $subtotal = 0;
        $totalItems = 0;

        foreach ($cart as $item) {
            $producto = Producto::with('detalles')->find($item['id'] ?? null);

            if (!$producto || !$producto->activo || (int) $producto->tipo_producto_id !== 1 || $producto->detalles->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Uno de los productos del carrito ya no está disponible para compra inmediata.',
                ];
            }

            $cantidad = max((int) ($item['cantidad'] ?? 0), 0);
            if ($cantidad === 0) {
                continue;
            }

            $lineSubtotal = round((float) $producto->preciopro * $cantidad, 2);
            $subtotal += $lineSubtotal;
            $totalItems += $cantidad;
            $items[] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'line_subtotal' => $lineSubtotal,
            ];
        }

        if (empty($items)) {
            return [
                'success' => false,
                'message' => 'Tu carrito no contiene productos válidos.',
            ];
        }

        $discount = $this->getCartDiscount($totalItems);
        $discount = min($discount, round($subtotal, 2));
        $total = round($subtotal - $discount, 2);
        $remainingTotal = $total;

        foreach ($items as $index => &$item) {
            $item['line_total'] = $index === count($items) - 1
                ? round($remainingTotal, 2)
                : round(($item['line_subtotal'] / $subtotal) * $total, 2);

            $remainingTotal -= $item['line_total'];
        }
        unset($item);

        return [
            'success' => true,
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
            'total_items' => $totalItems,
        ];
    }

    private function getCartDiscount(int $totalItems): float
    {
        if ($totalItems === 2) {
            return 0.75;
        }

        if ($totalItems === 3) {
            return 1.40;
        }

        if ($totalItems === 4) {
            return 2.00;
        }

        if ($totalItems >= 5) {
            return 2.50;
        }

        return 0.0;
    }

    private function sendInvoiceIfPossible(Venta $venta): void
    {
        if (empty($venta->cliente?->email)) {
            return;
        }

        try {
            Mail::to($venta->cliente->email)->send(new facturaMail($venta));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar factura de compra desde shop', [
                'venta_id' => $venta->idven,
                'cliente_id' => $venta->idcli,
                'error' => $e->getMessage(),
            ]);
        }
    }
    private function verificarCuentaLlena($cuenta, $producto)
    {
        $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
        if ($usuariosActivos >= $cuenta->valor->pantmaxval) {
            //$producto->update(['activo' => false]);
        }
    }
}
