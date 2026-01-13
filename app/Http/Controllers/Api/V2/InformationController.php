<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    public function __construct()
    {
        // Forzar que todas las respuestas sean JSON
        request()->headers->set('Accept', 'application/json');
    }

    public function getPrecios(Request $request)
    {
        try {
            // Obtener tipo de mensaje solicitado
            $tipo = $request->input('tipo', 'general'); // general, productos, combos, servicio

            switch ($tipo) {
                case 'general':
                    $mensaje = $this->generarMensajeGeneral();
                    break;
                case 'productos':
                    $mensaje = $this->generarMensajeProductos();
                    break;
                case 'combos':
                    $mensaje = $this->generarMensajeCombos();
                    break;
                case 'servicio':
                    $servicioId = $request->input('servicio_id');
                    if (!$servicioId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Se requiere el parámetro servicio_id para este tipo'
                        ], 400);
                    }
                    $mensaje = $this->generarMensajeServicio($servicioId);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de mensaje no válido. Tipos disponibles: general, productos, combos, servicio'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo' => $tipo,
                    'mensaje' => $mensaje
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el mensaje de precios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera mensaje de precios generales basado en servicios configurados
     */
    protected function generarMensajeGeneral()
    {
        $servicios = Servicio::all();
        $serviciosConfig = $this->obtenerServiciosConConfig($servicios);

        $mensaje = "*Precios 1 mes 1 disp* 🍿\n";

        foreach ($serviciosConfig as $key => $servicioInfo) {
            if (isset($servicioInfo['precioser'])) {
                $mensaje .= "{$servicioInfo['nombre']}: \${$servicioInfo['precioser']}\n";
            }
        }

        return $mensaje;
    }

    /**
     * Genera mensaje de precios de productos individuales con meses = 1
     */
    protected function generarMensajeProductos()
    {
        $productos = Producto::with(['detalles', 'categoria'])
            ->whereHas('categoria', function($query) {
                $query->where('nombre', 'Individual');
            })
            ->whereHas('detalles', function($query) {
                $query->where('meses', 1);
            })
            ->get();

        $mensaje = "*Precios 1 mes 1 disp* 🍿\n";

        foreach ($productos as $producto) {
            $detalle = $producto->detalles->where('meses', 1)->first();
            if ($detalle) {
                $mensaje .= "{$producto->nombrepro}: \${$producto->preciopro}\n";
            }
        }

        return $mensaje;
    }

    /**
     * Genera mensaje de precios de combos con meses = 1
     */
    protected function generarMensajeCombos()
    {
        $productos = Producto::with(['detalles', 'categoria'])
            ->whereHas('categoria', function($query) {
                $query->where('nombre', 'Combos');
            })
            ->whereHas('detalles', function($query) {
                $query->where('meses', 1);
            })
            ->get();

        $mensaje = "*Combos 1 mes 1 disp* 🍿\n";

        foreach ($productos as $producto) {
            $detalle = $producto->detalles->where('meses', 1)->first();
            if ($detalle) {
                $mensaje .= "{$producto->nombrepro}: \${$producto->preciopro}\n";
            }
        }

        return $mensaje;
    }

    /**
     * Genera mensaje de planes de un servicio específico
     */
    protected function generarMensajeServicio($servicioId)
    {
        $servicio = Servicio::find($servicioId);

        if (!$servicio) {
            return "Servicio no encontrado";
        }

        $productos = Producto::with(['detalles'])
            ->whereHas('detalles', function($query) use ($servicioId) {
                $query->where('idser', $servicioId);
            })
            ->get();

        $mensaje = "*Planes de {$servicio->nombreser}* 🍿\n\n";

        foreach ($productos as $producto) {
            $detallesServicio = $producto->detalles->where('idser', $servicioId);

            if ($detallesServicio->isNotEmpty()) {
                $mensaje .= "{$producto->nombrepro}:\n";

                foreach ($detallesServicio as $detalle) {
                    $mensaje .= "  - {$detalle->meses} mes(es): \${$producto->preciopro}\n";
                }

                $mensaje .= "\n";
            }
        }

        return $mensaje;
    }

    /**
     * Obtiene la configuración de servicios con sus precios
     */
    protected function obtenerServiciosConConfig($servicios)
    {
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

        foreach ($servicios as $servicio) {
            if (isset($serviciosConfig[$servicio->idser])) {
                $serviciosConfig[$servicio->idser]['precioser'] = $servicio->precioser;
                $serviciosConfig[$servicio->idser]['comboser'] = $servicio->comboser;
            }
        }

        return $serviciosConfig;
    }
}
