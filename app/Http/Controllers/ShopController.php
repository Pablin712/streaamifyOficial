<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
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

        return view('shopping.index', compact(
            'productosInmediataIndividual',
            'productosCombos',
            'productosPedidos',
            'productosPersonalizados',
            'productosCompletos'
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

    // Eliminar un producto del carrito
    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return response()->json(['message' => 'Producto eliminado del carrito', 'cart' => $cart]);
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

    public function comprar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // Aquí puedes agregar la lógica de compra, como guardar en la base de datos
        // y enviar confirmaciones de compra.

        // Guardamos la compra en la sesión para mostrar en el modal de éxito
        session()->flash('compra_exitosa', [
            'nombre' => $producto->nombrepro,
            'precio' => $producto->preciopro
        ]);

        return redirect()->route('shop');
    }
}
