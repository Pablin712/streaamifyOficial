<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

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
}
