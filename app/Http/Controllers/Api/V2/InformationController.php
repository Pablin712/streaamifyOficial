<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Banco;
use App\Models\ViewUsuarioActivo;
use App\Models\Cuenta;
use App\Models\Cliente;
use App\Models\DailyStatistic;
use App\Models\ViewClientesUsuarios;
use Carbon\Carbon;
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
     * Obtiene información de todos los métodos de pago (bancos)
     */
    public function getMetodosPago()
    {
        try {
            $mensaje = $this->generarMensajeMetodosPagoGeneral();

            return response()->json([
                'success' => true,
                'data' => [
                    'mensaje' => $mensaje
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el mensaje de métodos de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene información de un banco específico
     */
    public function getBanco($id)
    {
        try {
            $mensaje = $this->generarMensajeBancoEspecifico($id);

            if ($mensaje === "Método de pago no encontrado") {
                return response()->json([
                    'success' => false,
                    'message' => 'Banco no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'mensaje' => $mensaje
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la información del banco',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene resumen de tareas diarias (para WhatsApp)
     * GET /api/v2/info/tareas-hoy
     */
    public function getTareasHoy()
    {
        try {
            $hoy = Carbon::today();
            $ayer = Carbon::yesterday();

            // 1. Total usuarios atrasados (fecha_vencimiento < hoy)
            $usuariosAtrasados = ViewUsuarioActivo::where('fecha_vencimiento', '<', $hoy)->count();

            // 2. Total usuarios a cobrar HOY (fecha_vencimiento = hoy)
            $usuariosCobrarHoy = ViewUsuarioActivo::whereDate('fecha_vencimiento', $hoy)->count();

            // 3. Usuarios a cobrar en 3 días (pending payments)
            $fechaLimite = $hoy->copy()->addDays(3);
            $usuariosPendientes = ViewUsuarioActivo::whereBetween('fecha_vencimiento', [$hoy, $fechaLimite])->count();

            // 4. Total cuentas caídas (activocue=true, caidacue=true, no es mesa de trabajo)
            $cuentasCaidas = Cuenta::where('activocue', true)
                ->where('caidacue', true)
                ->where('idcue', 'NOT LIKE', '%Atencion%')
                ->count();

            // 5. Total de clientes
            $totalClientes = ViewClientesUsuarios::count();

            // 6. Total de usuarios activos
            $totalUsuarios = ViewUsuarioActivo::count();

            // 7 y 8. Estadísticas de ayer (ingresos y ventas)
            $estadisticasAyer = DailyStatistic::whereDate('date', $ayer)->first();
            $ingresosAyer = $estadisticasAyer->daily_revenue ?? 0;
            $ventasAyer = $estadisticasAyer->daily_sales ?? 0;

            // 9. Total cuentas vencidas (usuarios atrasados agrupados por cuenta)
            $cuentasVencidas = ViewUsuarioActivo::where('fecha_vencimiento', '<', $hoy)
                ->distinct('idcue')
                ->count('idcue');

            // Generar mensaje para WhatsApp
            $mensaje = "*📋 Tareas de Hoy - " . $hoy->format('d/m/Y') . "*\n\n";

            $mensaje .= "🔴 *Usuarios atrasados:* {$usuariosAtrasados}\n";
            $mensaje .= "💰 *Cobrar hoy:* {$usuariosCobrarHoy}\n";
            $mensaje .= "⏰ *Pendientes (3 días):* {$usuariosPendientes}\n";
            $mensaje .= "⚠️ *Cuentas caídas:* {$cuentasCaidas}\n";
            $mensaje .= "📦 *Cuentas vencidas:* {$cuentasVencidas}\n\n";

            $mensaje .= "👥 *Total clientes:* {$totalClientes}\n";
            $mensaje .= "👤 *Total usuarios:* {$totalUsuarios}\n\n";

            $mensaje .= "*📊 Ayer (" . $ayer->format('d/m') . ")*\n";
            $mensaje .= "💵 Ingresos: \$" . number_format($ingresosAyer, 2) . "\n";
            $mensaje .= "🛒 Ventas: {$ventasAyer}\n";

            return response()->json([
                'success' => true,
                'data' => [
                    'fecha' => $hoy->format('Y-m-d'),
                    'resumen' => [
                        'usuarios_atrasados' => $usuariosAtrasados,
                        'cobrar_hoy' => $usuariosCobrarHoy,
                        'pendientes_3dias' => $usuariosPendientes,
                        'cuentas_caidas' => $cuentasCaidas,
                        'cuentas_vencidas' => $cuentasVencidas,
                        'total_clientes' => $totalClientes,
                        'total_usuarios' => $totalUsuarios,
                    ],
                    'ayer' => [
                        'fecha' => $ayer->format('Y-m-d'),
                        'ingresos' => round($ingresosAyer, 2),
                        'ventas' => $ventasAyer
                    ],
                    'mensaje' => $mensaje
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas del día',
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

    /**
     * Genera mensaje general con todos los métodos de pago
     */
    protected function generarMensajeMetodosPagoGeneral()
    {
        $bancos = Banco::orderBy('nombreban')->get();

        if ($bancos->isEmpty()) {
            return "No hay métodos de pago disponibles";
        }

        // Usar el primer banco para obtener los datos del propietario
        $primerBanco = $bancos->first();

        $mensaje = "*Para realizar pagos* 💰\n\n";
        $mensaje .= "*Owner:* {$primerBanco->propietarioban}\n";
        $mensaje .= "*CI:* {$primerBanco->cedulaban}\n";

        // Email fijo (no está en la tabla bancos)
        $email = "pablojimenezelizalde@gmail.com";
        $mensaje .= "*Mail:* {$email}\n\n";

        foreach ($bancos as $banco) {
            $tipoCuenta = $banco->tipoban ?? 'Cuenta';
            $mensaje .= "*{$tipoCuenta} {$banco->nombreban}*\n";
            $mensaje .= "{$banco->numeroban}\n\n";
        }

        return $mensaje;
    }

    /**
     * Genera mensaje de un banco específico
     */
    protected function generarMensajeBancoEspecifico($nombrebanco)
    {
        //busque like e ignorecase
        $banco = Banco::where('nombreban', 'LIKE', "%{$nombrebanco}%")->first();

        if (!$banco) {
            return "Método de pago no encontrado";
        }

        $tipoCuenta = $banco->tipoban ?? 'Cuenta';

        // Email fijo (no está en la tabla bancos)
        $email = "pablojimenezelizalde@gmail.com";

        $mensaje = "*Información de Pago* 💰\n\n";
        $mensaje .= "*Owner:* {$banco->propietarioban}\n";
        $mensaje .= "*CI:* {$banco->cedulaban}\n";
        $mensaje .= "*Mail:* {$email}\n\n";
        $mensaje .= "*{$tipoCuenta} {$banco->nombreban}*\n";
        $mensaje .= "{$banco->numeroban}\n";

        if ($banco->detalleban) {
            $mensaje .= "\n*Detalles:* {$banco->detalleban}\n";
        }

        return $mensaje;
    }
}
