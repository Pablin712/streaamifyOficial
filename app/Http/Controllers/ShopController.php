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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\facturaMail;
use Carbon\Carbon;

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
            ->where('activo', true)
            ->get();

        $productosPersonalizados = Producto::where('tipo_producto_id', 3) // Personalizado
            ->where('activo', true)
            ->get();

        $productosCompletos = Producto::where('categoria_id', 3) // Categoría Completo
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
            'cart'
        ));
    }
    // Añadir un producto al carrito
    public function addToCart(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
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
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop')->with('error', 'Tu carrito está vacío.');
        }
        foreach ($cart as $item) {
            $producto = Producto::findOrFail($item['id']);
            $cantidad = $item['cantidad'];
            for($i = 0; $i < $cantidad; $i++) {
                foreach ($producto->detalles as $detalle) {
                    if (!$this->buscarCuentaDisponible($detalle->idser)) {
                        // ❌ No hay cuentas disponibles: Desactivar el producto
                        $producto->update(['activo' => false]);
                        return back()->with('error', 'No hay cuentas disponibles para este servicio.');
                    }
                }
            }
        }

        $idCliente = Auth::guard('cliente')->user()->idcli;
        // Buscar el cliente en la base de datos
        $usuario = Cliente::findOrFail($idCliente);
        // Verificar saldo
        if ($usuario->saldo < $producto->preciopro) {
            return back()->with('error', 'Saldo insuficiente para realizar la compra.');
        }

        DB::beginTransaction();
        try {
            // Crear venta
            $venta = new Venta();
            $venta->idemp = 1;
            $venta->idcli = $usuario->idcli;
            $venta->fechaven = now();
            $venta->save();
            $mensajesServicios = []; // Inicialización correcta
            $preciofinal = 0;
            foreach ($cart as $item) {
                $producto = Producto::findOrFail($item['id']);
                $cantidad = $item['cantidad'];
                for($i = 0; $i < $cantidad; $i++) {
                    // Procesar cada detalle del producto
                    foreach ($producto->detalles as $detalle) {
                        // Buscar cuenta disponible
                        $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                        if (!$cuenta) {
                            $producto->update(['activo' => false]);
                            throw new \Exception("No hay cuentas disponibles para el servicio.");
                        }

                        // Buscar perfil disponible
                        $perfil = $this->buscarPerfilDisponible($cuenta);

                        if (!$perfil) {
                            throw new \Exception("No hay perfiles disponibles en la cuenta seleccionada.");
                        }

                        // Registrar detalle de venta
                        $detalleVenta = new DetalleVenta();
                        $detalleVenta->idven = $venta->idven;
                        $detalleVenta->idper = $perfil->idper;
                        $detalleVenta->fechavendet = now()->addMonths($detalle->meses)->subDay();
                        $detalleVenta->descripciondet = "Vendido en automático";
                        $detalleVenta->montodet = $producto->preciopro / count($producto->detalles);
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

            // Verificar si la cuenta se llenó
            $this->verificarCuentaLlena($cuenta, $producto);
            //dd($cuenta);
            // Guardar mensaje de éxito en sesión
            session()->flash('compra_exitosa', [
                'nombre' => 'Productos Comprados',
                'precio' => $preciofinal,
                'servicios' => $mensajesServicios // Aquí guardamos los servicios adquiridos
            ]);

            DB::commit();

            // Lógica para generar y enviar la factura por correo
            Mail::to($venta->cliente->email)->send(new facturaMail($venta));

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
            // 📌 Verificar disponibilidad antes de la transacción
            foreach ($producto->detalles as $detalle) {
                if (!$this->buscarCuentaDisponible($detalle->idser)) {
                    // ❌ No hay cuentas disponibles: Desactivar el producto
                    $producto->update(['activo' => false]);
                    return back()->with('error', 'No hay cuentas disponibles para este servicio.');
                }
            }
            DB::beginTransaction();
            try {
                // Crear venta
                $venta = new Venta();
                $venta->idemp = Empleado::where('nombreemp', 'Laravel')->value('idemp');
                $venta->idcli = $usuario->idcli;
                $venta->fechaven = now();
                $venta->save();
                // Procesar cada detalle del producto
                foreach ($producto->detalles as $detalle) {
                    // Buscar cuenta disponible
                    $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                    if (!$cuenta) {
                        $producto->update(['activo' => false]);
                        throw new \Exception("No hay cuentas disponibles para el servicio.");
                    }

                    // Buscar perfil disponible
                    $perfil = $this->buscarPerfilDisponible($cuenta);

                    if (!$perfil) {
                        throw new \Exception("No hay perfiles disponibles en la cuenta seleccionada.");
                    }

                    // Registrar detalle de venta
                    $detalleVenta = new DetalleVenta();
                    $detalleVenta->idven = $venta->idven;
                    $detalleVenta->idper = $perfil->idper;
                    $detalleVenta->fechavendet = now()->addMonths($detalle->meses)->subDay();
                    $detalleVenta->descripciondet = "Vendido en automático";
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
                    'servicios' => $mensajesServicios // Aquí guardamos los servicios adquiridos
                ]);

                DB::commit();

                // Lógica para generar y enviar la factura por correo
                Mail::to($venta->cliente->email)->send(new facturaMail($venta));

                return redirect()->route('shop');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        } else {
            // Registrar el pedido sin descontar saldo
            Pedido::create([
                'idcli' => $usuario->idcli,
                'producto_id' => $producto->id,
                //'idestado' => 1,
                'fechapedido' => now(),
                'respuesta' => 'Sin responder',
            ]);
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
        $idCliente = Auth::guard('cliente')->user()->idcli;
        $usuario = Cliente::findOrFail($idCliente);
        $venta = Venta::findOrFail($id);

        // Perfiles seleccionados por el usuario para renovar
        $detallesSeleccionados = $request->input('detalles', []);
        if (empty($detallesSeleccionados)) {
            return redirect()->back()->with('error', 'No has seleccionado ningún perfil para renovar.');
        }

        // Buscar el producto basado en los detalles seleccionados
        $producto = $this->buscarProductoPorDetalles($detallesSeleccionados);

        if (!$producto) {
            return redirect()->back()->with('error', 'No hay cuenta disponible para renovar.');
        }

        if ($producto->categoria_id == 1) { // Si es un producto de entrega inmediata
            return $this->storeRenew($idCliente, $venta, $producto, $detallesSeleccionados);
        } else {
            // Registrar el pedido sin descontar saldo
            Pedido::create([
                'idcli' => $usuario->idcli,
                'producto_id' => $producto->id,
                'fechapedido' => now(),
                'respuesta' => 'Sin responder. Renovación Pendiente',
            ]);

            session()->flash('pedido_registrado', [
                'nombre' => $producto->nombrepro,
                'precio' => $producto->preciopro
            ]);

            return redirect()->back();
        }
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

    private function storeRenew($idCliente, $ventaPasada, $producto, $detallesSeleccionados)
    {
        $detallesVenta = DetalleVenta::whereIn('iddet', $detallesSeleccionados)->get();

        if ($detallesVenta->isEmpty()) {
            return redirect()->back()->with('error', 'No hay detalles de venta disponibles.');
        }

        $total_venta = $detallesVenta->sum('montodet');
        $fecha = Carbon::today()->toDateString();

        // Crear la nueva venta
        $ventaNueva = Venta::create([
            'idcli' => $idCliente,
            'idemp' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,
        ]);

        // Desactivar los detalles anteriores solo para los seleccionados
        DetalleVenta::whereIn('iddet', $detallesSeleccionados)->update(['activodet' => false]);

        foreach ($detallesVenta as $detalle) {
            DetalleVenta::create([
                'idven' => $ventaNueva->idven,
                'idper' => $detalle->idper,
                'descripciondet' => $detalle->descripciondet,
                'fechavendet' => Carbon::parse($detalle->fechavendet)->addMonth(),
                'montodet' => $detalle->montodet,
                'activodet' => true,
            ]);
        }

        // Registrar en historial
        Historial::create([
            'accion' => 'Se renovó la venta con ID: ' . $ventaPasada->idven,
            'descripcion' => 'Nueva venta creada con ID ' . $ventaNueva->idven,
            'realizado_por' => 'Laravel | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $usuario = Cliente::where('idcli', $idCliente)->firstOrFail();
        // Descontar saldo al usuario
        $usuario->saldo -= $producto->preciopro;
        $usuario->save();

        return redirect()->back()->with('renovacion_exitosa', [
            'nombre' => $ventaNueva->detalles_venta->first()->perfil->cuenta->valor->servicio->nombreser,
            'fecha_vencimiento' => $ventaNueva->detalles_venta->first()->fechavendet->format('d/m/Y'),
        ]);
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

    private function buscarPerfilDisponible($cuenta)
    {
        // Primero, intenta encontrar un perfil con 0 usuarios activos
        $perfil = Perfil::where('idcue', $cuenta->idcue)
            ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos 
                    WHERE view_usuarios_activos.idcue = perfiles.idcue 
                    AND view_usuarios_activos.perfil = perfiles.numeroper) = 0')
            ->first();

        // Si no hay perfiles con 0 usuarios, intenta encontrar uno con solo 1 usuario activo
        if (!$perfil) {
            $perfil = Perfil::where('idcue', $cuenta->idcue)
                ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos 
                        WHERE view_usuarios_activos.idcue = perfiles.idcue 
                        AND view_usuarios_activos.perfil = perfiles.numeroper) = 1')
                ->first();
        }
        return $perfil; // Retorna el perfil encontrado o null si no hay ninguno disponible
    }

    private function enviarMensajeEntrega($perfil)
    {
        $mensaje = "**" . $perfil->cuenta->valor->servicio->nombre . "**\n" .
            "Usuario: " . $perfil->cuenta->usuariocue . "\n" .
            "Clave: " . $perfil->cuenta->contrasenacue . "\n" .
            "PIN perfil #{$perfil->numeroper}: " . $perfil->pinper;

        session()->flash('mensaje_entrega', $mensaje);
    }

    private function verificarCuentaLlena($cuenta, $producto)
    {
        $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
        if ($usuariosActivos >= $cuenta->valor->pantmaxval) {
            //$producto->update(['activo' => false]);
        }
    }
}
