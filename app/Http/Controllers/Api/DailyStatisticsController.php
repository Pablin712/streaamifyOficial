<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DailyStatisticsController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Guardar estadísticas diarias (endpoint para cron)
     * GET/POST /api/daily-statistics/save
     *
     * Parámetros opcionales:
     * - date: fecha específica (formato: Y-m-d). Si no se envía, usa fecha actual
     * - token: token de seguridad (configurar en .env como CRON_TOKEN)
     */
    public function save(Request $request)
    {
        try {
            // Obtener fecha (por defecto hoy)
            $date = $request->input('date')
                ? Carbon::parse($request->input('date'))->format('Y-m-d')
                : Carbon::today()->format('Y-m-d');

            // Guardar estadísticas
            $result = $this->dashboardService->guardar($date);

            // Log para registro
            Log::info('Estadísticas diarias guardadas', [
                'date' => $date,
                'revenue' => $result['data']['daily_revenue'],
                'cost' => $result['data']['daily_cost'],
                'balance' => $result['data']['balance'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas guardadas correctamente',
                'date' => $date,
                'data' => $result['data']
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al guardar estadísticas diarias', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar estadísticas de un rango de fechas (útil para rellenar histórico)
     * POST /api/daily-statistics/save-range
     *
     * Parámetros:
     * - start_date: fecha inicial (Y-m-d)
     * - end_date: fecha final (Y-m-d)
     * - token: token de seguridad
     */
    public function saveRange(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Limitar a máximo 90 días para evitar timeout
            if ($startDate->diffInDays($endDate) > 90) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rango máximo es de 90 días'
                ], 400);
            }

            $saved = 0;
            $errors = [];
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                try {
                    $this->dashboardService->guardar($currentDate->format('Y-m-d'));
                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'error' => $e->getMessage()
                    ];
                }
                $currentDate->addDay();
            }

            Log::info('Estadísticas de rango guardadas', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'saved' => $saved,
                'errors' => count($errors)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Estadísticas guardadas: $saved días",
                'saved' => $saved,
                'errors' => $errors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de un día específico
     * GET /api/daily-statistics/{date}
     */
    public function show($date)
    {
        try {
            $statistic = \App\Models\DailyStatistic::where('date', $date)->first();

            if (!$statistic) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay estadísticas para esta fecha'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $statistic
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
