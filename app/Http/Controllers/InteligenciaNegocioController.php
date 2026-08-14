<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\InteligenciaNegocioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InteligenciaNegocioController extends Controller
{
    public function __construct(
        private InteligenciaNegocioService $service,
        private DashboardService $dashboardService,
    ) {
    }

    public function index(Request $request)
    {
        if (!Gate::allows('dashboard')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        // ── Tab Resumen: comparativa de los 9 servicios ──────────────
        $servicios = [
            'Netflix' => $this->dashboardService->getNetflix($month, $year),
            'Disney' => $this->dashboardService->getDisney($month, $year),
            'Prime' => $this->dashboardService->getPrime($month, $year),
            'Max' => $this->dashboardService->getMax($month, $year),
            'Magis' => $this->dashboardService->getMagis($month, $year),
            'Crunchyroll' => $this->dashboardService->getCrunchyroll($month, $year),
            'Paramount' => $this->dashboardService->getParamount($month, $year),
            'Spotify' => $this->dashboardService->getSpotify($month, $year),
            'Otros' => $this->dashboardService->getOtros($month, $year),
        ];
        $comparativaServicios = collect($servicios)->map(function ($datos, $nombre) {
            $vals = array_values($datos);
            [$cuentas, $usuarios, $ingresos, $costos] = $vals;
            return [
                'nombre' => $nombre,
                'cuentas' => $cuentas,
                'usuarios' => $usuarios,
                'ingresos' => (float) $ingresos,
                'costos' => (float) $costos,
                'ganancia' => (float) $ingresos - (float) $costos,
            ];
        })->sortByDesc('ganancia')->values();

        // ── Tab Ventas ────────────────────────────────────────────────
        $desgloseVentasMes = $this->service->desgloseVentasPorTipo(
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );
        $ventasMensuales = $this->service->ventasMensualesPorTipo(12);

        // ── Tab Servicios ────────────────────────────────────────────
        $nuevosPerdidos = $this->service->nuevosPerdidosPorServicio(12);

        // ── Tab Clientes (RFM paginado) ─────────────────────────────
        $page = (int) $request->input('page', 1);
        $segmento = $request->input('segmento');
        $search = $request->input('buscar');
        $clientesRFM = $this->service->clientesRFM($page, 25, $segmento, $search);
        $resumenSegmentos = $this->service->resumenSegmentos();
        $segmentos = $this->service->segmentos();

        // ── Tab Retención ────────────────────────────────────────────
        $cohortes = $this->service->cohortesRetencion(12);

        return view('finance.inteligencia.index', compact(
            'comparativaServicios',
            'desgloseVentasMes',
            'ventasMensuales',
            'nuevosPerdidos',
            'clientesRFM',
            'resumenSegmentos',
            'segmentos',
            'segmento',
            'search',
            'cohortes'
        ));
    }
}
