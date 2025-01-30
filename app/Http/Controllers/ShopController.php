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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ShopController extends Controller
{
    public function index()
    {
        $productosInmediataIndividual = Producto::where('tipo_producto_id', 1) // Entrega Inmediata
            ->where('categoria_id', 1) // Categoría Individual
            ->where('activo', true)
            ->get();

        $productosCombos = Producto::where('categoria_id', 2) // Categoría Combo
            ->where('activo', true)
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
        return response()->json(['message' => 'Producto añadido al carrito', 'cart' => $cart]);
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

        return view('cliente.checkout', compact('cart'));
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
                $this->verificarCuentaLlena($cuenta);
                //dd($cuenta);
                // Guardar mensaje de éxito en sesión
                session()->flash('compra_exitosa', [
                    'nombre' => $producto->nombrepro,
                    'precio' => $producto->preciopro,
                    'servicios' => $mensajesServicios // Aquí guardamos los servicios adquiridos
                ]);

                DB::commit();
                return redirect()->route('shop');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        } else {
            // Registrar pedido sin descontar saldo
            session()->flash('pedido_registrado', [
                'nombre' => $producto->nombrepro,
                'precio' => $producto->preciopro
            ]);
            return redirect()->route('shop');
        }
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
        return Perfil::where('idcue', $cuenta->idcue)
        ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos 
                    WHERE view_usuarios_activos.idcue = perfiles.idcue 
                    AND view_usuarios_activos.perfil = perfiles.numeroper) <= 1')
        ->first();
    }

    private function enviarMensajeEntrega($perfil)
    {
        $mensaje = "**" . $perfil->cuenta->valor->servicio->nombre . "**\n" .
            "Usuario: " . $perfil->cuenta->usuariocue . "\n" .
            "Clave: " . $perfil->cuenta->contrasenacue . "\n" .
            "PIN perfil #{$perfil->numeroper}: " . $perfil->pinper;

        session()->flash('mensaje_entrega', $mensaje);
    }

    private function verificarCuentaLlena($cuenta)
    {
        $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
        if ($usuariosActivos >= $cuenta->valor->pantmaxval) {
            $cuenta->update(['activocue' => false]);
        }
    }
}
