<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ContadorService;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para el Rol Contador
 * Gestiona todas las operaciones financieras y estadísticas para empleados contadores
 *
 * Rutas: /api/v2/accountant/*
 */
class ContadorController extends Controller
{
    protected $contadorService;

    public function __construct(ContadorService $contadorService)
    {
        $this->contadorService = $contadorService;

        // Forzar respuestas JSON
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * A1. Resumen de Ventas
     * GET /api/v2/accountant/ventas/resumen
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumenVentas(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periodo' => 'nullable|in:daily,weekly,monthly,yearly,custom',
                'fecha_inicio' => 'nullable|date|required_if:periodo,custom',
                'fecha_fin' => 'nullable|date|required_if:periodo,custom|after_or_equal:fecha_inicio'
            ], [
                'periodo.in' => 'El período debe ser: daily, weekly, monthly, yearly o custom',
                'fecha_inicio.required_if' => 'La fecha de inicio es requerida para período custom',
                'fecha_fin.required_if' => 'La fecha de fin es requerida para período custom',
                'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $periodo = $request->input('periodo', 'daily');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            $data = $this->contadorService->resumenVentas($periodo, $fechaInicio, $fechaFin);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen de ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A2. Facturas Pendientes
     * GET /api/v2/accountant/ventas/facturas-pendientes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function facturasPendientes(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dias_proximos' => 'nullable|integer|min:1|max:30',
                'servicio' => 'nullable|integer|exists:servicios,idser'
            ], [
                'dias_proximos.integer' => 'Los días próximos deben ser un número',
                'dias_proximos.min' => 'Los días próximos deben ser al menos 1',
                'dias_proximos.max' => 'Los días próximos no pueden superar 30',
                'servicio.exists' => 'El servicio especificado no existe'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $diasProximos = $request->input('dias_proximos', 3);
            $servicio = $request->input('servicio');

            $data = $this->contadorService->facturasPendientes($diasProximos, $servicio);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas pendientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A3. Ingresos por Servicio
     * GET /api/v2/accountant/ventas/ingresos-por-servicio
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ingresosPorServicio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periodo' => 'nullable|in:daily,weekly,monthly,yearly',
                'fecha' => 'nullable|date'
            ], [
                'periodo.in' => 'El período debe ser: daily, weekly, monthly o yearly'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $periodo = $request->input('periodo', 'monthly');
            $fecha = $request->input('fecha');

            $data = $this->contadorService->ingresosPorServicio($periodo, $fecha);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ingresos por servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A4. Clientes Morosos
     * GET /api/v2/accountant/clientes/morosos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clientesMorosos(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dias_minimos' => 'nullable|integer|min:1'
            ], [
                'dias_minimos.integer' => 'Los días mínimos deben ser un número',
                'dias_minimos.min' => 'Los días mínimos deben ser al menos 1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $diasMinimos = $request->input('dias_minimos', 3);

            $data = $this->contadorService->clientesMorosos($diasMinimos);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes morosos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A5. Proyección de Ingresos
     * GET /api/v2/accountant/ventas/proyeccion
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function proyeccionIngresos(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periodo' => 'nullable|in:next_week,next_month'
            ], [
                'periodo.in' => 'El período debe ser: next_week o next_month'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $periodo = $request->input('periodo', 'next_month');

            $data = $this->contadorService->proyeccionIngresos($periodo);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proyección de ingresos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A6. Estadísticas de Clientes
     * GET /api/v2/accountant/clientes/estadisticas
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticasClientes(Request $request)
    {
        try {
            $data = $this->contadorService->estadisticasClientes();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A7. Estadísticas de Métodos de Pago
     * GET /api/v2/accountant/metodos-pago/estadisticas
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticasMetodosPago(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periodo' => 'nullable|in:daily,weekly,monthly,yearly'
            ], [
                'periodo.in' => 'El período debe ser: daily, weekly, monthly o yearly'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $periodo = $request->input('periodo', 'monthly');

            $data = $this->contadorService->estadisticasMetodosPago($periodo);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de métodos de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A8. Reporte General Completo
     * GET /api/v2/accountant/reportes/general
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reporteGeneral(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'periodo' => 'nullable|in:daily,weekly,monthly,yearly'
            ], [
                'periodo.in' => 'El período debe ser: daily, weekly, monthly o yearly'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            $periodo = $request->input('periodo', 'monthly');

            $data = $this->contadorService->reporteGeneral($periodo);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Reporte generado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte general',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * A9. Exportar Reporte (Preparado para futuro)
     * POST /api/v2/accountant/reportes/exportar
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportarReporte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo_reporte' => 'required|in:ventas,clientes,servicios,general',
                'formato' => 'nullable|in:pdf,excel,csv',
                'periodo' => 'nullable|in:daily,weekly,monthly,yearly'
            ], [
                'tipo_reporte.required' => 'El tipo de reporte es requerido',
                'tipo_reporte.in' => 'El tipo de reporte debe ser: ventas, clientes, servicios o general',
                'formato.in' => 'El formato debe ser: pdf, excel o csv'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            // TODO: Implementar exportación a PDF/Excel/CSV
            // Por ahora retornar los datos en JSON

            $tipoReporte = $request->input('tipo_reporte');
            $periodo = $request->input('periodo', 'monthly');

            $data = null;
            switch ($tipoReporte) {
                case 'ventas':
                    $data = $this->contadorService->resumenVentas($periodo);
                    break;
                case 'clientes':
                    $data = $this->contadorService->estadisticasClientes();
                    break;
                case 'servicios':
                    $data = $this->contadorService->ingresosPorServicio($periodo);
                    break;
                case 'general':
                    $data = $this->contadorService->reporteGeneral($periodo);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Datos preparados para exportación (función en desarrollo)'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
