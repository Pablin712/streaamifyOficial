<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TareasPendientes;
use App\Models\Tarea;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\Venta;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Carbon;

class EmpleadoService
{
    public function obtenerAsistenciasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }

        // Convertir la fecha a la zona horaria de la aplicación
        $fechaInicio = Carbon::parse($fecha)->startOfDay();
        $fechaFin = Carbon::parse($fecha)->endOfDay();

        // Obtener todas las asistencias del empleado para el día especificado
        // Laravel maneja automáticamente la conversión de timezone
        return Asistencia::where('empleado_id', $idemp)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at')
            ->get();
    }
    public function obtenerLapsosDeAsistenciasPorDia(int $idemp, string $fecha)
    {
        $asistencias = $this->obtenerAsistenciasPorDia($idemp, $fecha);
        return $this->calcularLapsos($asistencias);
    }

    /**
     * Agrupa una colección de asistencias (ya filtradas, normalmente de un mismo día)
     * en lapsos de conexión real: deduplica pings del mismo intervalo de 30s (múltiples
     * pestañas) y corta el lapso cuando el salto entre pings supera los 5 minutos.
     */
    private function calcularLapsos($asistencias): array
    {
        $lapsos = [];
        $total_conexion = 0;

        if ($asistencias->count() === 0) {
            return [
                'lapsos' => [],
                'total_conexion' => 0,
                'horas_conexion' => 0,
            ];
        }

        // DEDUPLICAR: Agrupar asistencias que estén en el mismo intervalo de 30 segundos
        // Esto elimina el problema de múltiples pestañas
        $asistenciasDeduplicadas = [];
        $ultimaAsistencia = null;

        foreach ($asistencias as $asistencia) {
            // created_at ya es un objeto Carbon por el cast del modelo
            $actual = $asistencia->created_at;

            // Si no hay última asistencia o han pasado más de 30 segundos, agregar
            if ($ultimaAsistencia === null ||
                abs($ultimaAsistencia->diffInSeconds($actual)) > 30) {
                $asistenciasDeduplicadas[] = $actual->copy();
                $ultimaAsistencia = $actual->copy();
            }
            // Si están en el mismo intervalo de 30s, ignorar (es un duplicado)
        }

        // Si después de deduplicar no hay asistencias, retornar vacío
        if (count($asistenciasDeduplicadas) === 0) {
            return [
                'lapsos' => [],
                'total_conexion' => 0,
                'horas_conexion' => 0,
            ];
        }

        // Si solo hay una asistencia, considerar un lapso mínimo de 1 minuto
        if (count($asistenciasDeduplicadas) === 1) {
            return [
                'lapsos' => [[
                    'inicio' => $asistenciasDeduplicadas[0]->copy(),
                    'fin' => $asistenciasDeduplicadas[0]->copy(),
                    'tiempo_conexion' => 1.0,
                ]],
                'total_conexion' => 1.0,
                'horas_conexion' => 0.02,
            ];
        }

        // Calcular lapsos con asistencias deduplicadas
        $inicio = $asistenciasDeduplicadas[0]->copy();
        $fin = $asistenciasDeduplicadas[0]->copy();

        for ($i = 1; $i < count($asistenciasDeduplicadas); $i++) {
            $anterior = $asistenciasDeduplicadas[$i - 1];
            $actual = $asistenciasDeduplicadas[$i];

            // Si la diferencia es menor o igual a 5 minutos, extender el lapso
            if ($actual->diffInMinutes($anterior) <= 5) {
                $fin = $actual->copy();
            } else {
                // Guardar lapso anterior
                $duracion = $inicio->floatDiffInMinutes($fin);
                // Considerar al menos 1 minuto si la duración es 0
                if ($duracion == 0) {
                    $duracion = 1.0;
                }
                $lapsos[] = [
                    'inicio' => $inicio->copy(),
                    'fin' => $fin->copy(),
                    'tiempo_conexion' => round($duracion, 2),
                ];
                $total_conexion += $duracion;

                // Iniciar nuevo lapso
                $inicio = $actual->copy();
                $fin = $actual->copy();
            }
        }

        // Último lapso
        $duracion = $inicio->floatDiffInMinutes($fin);
        // Considerar al menos 1 minuto si la duración es 0
        if ($duracion == 0) {
            $duracion = 1.0;
        }
        $lapsos[] = [
            'inicio' => $inicio->copy(),
            'fin' => $fin->copy(),
            'tiempo_conexion' => round($duracion, 2),
        ];
        $total_conexion += $duracion;

        return [
            'lapsos' => $lapsos,
            'total_conexion' => round($total_conexion, 2),
            'horas_conexion' => round($total_conexion / 60, 2),
        ];
    }

    /**
     * Total de tiempo realmente conectado (suma de lapsos, con huecos descontados)
     * de un empleado dentro de un rango de fechas — usado por el ranking de
     * "top empleados que más se conectan" en Rendimiento.
     */
    public function obtenerConexionEnRango(int $idemp, string $desde, string $hasta): array
    {
        $asistencias = Asistencia::where('empleado_id', $idemp)
            ->whereBetween('created_at', [
                Carbon::parse($desde)->startOfDay(),
                Carbon::parse($hasta)->endOfDay(),
            ])
            ->orderBy('created_at')
            ->get();

        $porDia = $asistencias->groupBy(fn($a) => $a->created_at->format('Y-m-d'));

        $totalMinutos = 0.0;
        foreach ($porDia as $grupoDelDia) {
            $totalMinutos += $this->calcularLapsos($grupoDelDia)['total_conexion'];
        }

        return [
            'total_minutos'  => round($totalMinutos, 2),
            'total_horas'    => round($totalMinutos / 60, 2),
            'dias_conectado' => $porDia->count(),
        ];
    }
    public function obtenerVentasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Venta::where('idemp', $idemp)
            ->whereDate('fechaven', $fecha)
            ->get();
    }
    public function contarVentasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Venta::where('idemp', $idemp)
            ->whereDate('fechaven', $fecha)
            ->count();
    }
    public function obtenerGestionRecargasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where('accion', 'Recarga-Procesada')
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionRecargasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where('accion', 'Recarga-Procesada')
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionProductosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where('accion', 'like', '%Producto%') // Buscar texto que contenga "Producto"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionProductosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where('accion', 'like', '%Producto%') // Buscar texto que contenga "Producto"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionInventarioPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%Servicio%')
                    ->orWhere('accion', 'like', '%servicio%')
                    ->orWhere('accion', 'like', '%valor%')
                    ->orWhere('accion', 'like', '%Valor%')
                    ->orWhere('accion', 'like', '%Proveedor%')
                    ->orWhere('accion', 'like', '%proveedor%');
            }) // Buscar texto que contenga "Servicio"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionInventarioPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%Servicio%')
                    ->orWhere('accion', 'like', '%servicio%')
                    ->orWhere('accion', 'like', '%valor%')
                    ->orWhere('accion', 'like', '%Valor%')
                    ->orWhere('accion', 'like', '%Proveedor%')
                    ->orWhere('accion', 'like', '%proveedor%');
            }) // Buscar texto que contenga "Servicio"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionCuentasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%cuenta%')
                    ->orWhere('accion', 'like', '%Cuenta%')
                    ->orWhere('accion', 'like', '%perfil%')
                    ->orWhere('accion', 'like', '%Perfil%')
                    ->orWhere('accion', 'like', '%usuario%')
                    ->orWhere('accion', 'like', '%Usuario%');
            }) // Buscar texto que contenga "Cuenta"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionCuentasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%cuenta%')
                    ->orWhere('accion', 'like', '%Cuenta%')
                    ->orWhere('accion', 'like', '%perfil%')
                    ->orWhere('accion', 'like', '%Perfil%')
                    ->orWhere('accion', 'like', '%usuario%')
                    ->orWhere('accion', 'like', '%Usuario%');
            }) // Buscar texto que contenga "Cuenta"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionVentasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%venta%')
                    ->orWhere('accion', 'like', '%Venta%');
            })
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function obtenerGestionTareasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%tarea%')
                    ->orWhere('accion', 'like', '%Tarea%');
            }) // Buscar texto que contenga "Tarea"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionTareasPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        $empleado = Empleado::find($idemp);
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return $empleado->tareasCompletadas()
            ->whereDate('fecha_completada', $fecha)
            ->count();
    }
    public function contarGestionCostosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%costo%')
                    ->orWhere('accion', 'like', '%Costo%');
            }) // Buscar texto que contenga "Costo"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionCostosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%costo%')
                    ->orWhere('accion', 'like', '%Costo%');
            }) // Buscar texto que contenga "Costo"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function obtenerGestionClientesPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%cliente%')
                    ->orWhere('accion', 'like', '%Cliente%');
            }) // Buscar texto que contenga "Cliente"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionClientesPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%cliente%')
                    ->orWhere('accion', 'like', '%Cliente%');
            }) // Buscar texto que contenga "Cliente"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerGestionGastosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%gasto%')
                    ->orWhere('accion', 'like', '%Gasto%');
            }) // Buscar texto que contenga "Gasto"
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function contarGestionGastosPorDia(int $idemp, string $fecha)
    {
        // Validar el formato de la fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido. Debe ser YYYY-MM-DD.');
        }
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%gasto%')
                    ->orWhere('accion', 'like', '%Gasto%');
            }) // Buscar texto que contenga "Gasto"
            ->whereDate('created_at', $fecha)
            ->count();
    }
    public function obtenerEstadisticasDelMes(int $idemp, string $nombreemp, int $mes, int $anio)
    {
        // Obtener todas las estadisticas del empleado para el mes y año especificados
        // en un arreglo se guardan los datos de cada dia del mes
        $estadisticas = [];
        $estadisticas['nombre'] = $nombreemp;
        for ($dia = 1; $dia <= 31; $dia++) {
            $fecha = Carbon::createFromDate($anio, $mes, $dia)->format('Y-m-d');
            if (Carbon::createFromDate($anio, $mes, $dia)->isValid()) {
                $totalConexion = $this->obtenerLapsosDeAsistenciasPorDia($idemp, $fecha)['horas_conexion'];

                $estadisticas[$fecha] = [
                    'asistencias' => $totalConexion,
                    'ventas' => $this->contarVentasPorDia($idemp, $fecha),
                    'recargas' => $this->contarGestionRecargasPorDia($idemp, $fecha),
                    'productos' => $this->contarGestionProductosPorDia($idemp, $fecha),
                    'inventario' => $this->contarGestionInventarioPorDia($idemp, $fecha),
                    'cuentas' => $this->contarGestionCuentasPorDia($idemp, $fecha),
                    'tareas' => $this->contarGestionTareasPorDia($idemp, $fecha),
                    'costos' => $this->contarGestionCostosPorDia($idemp, $fecha),
                    'clientes' => $this->contarGestionClientesPorDia($idemp, $fecha),
                    'gastos' => $this->contarGestionGastosPorDia($idemp, $fecha),
                ];
            }
        }
        return $estadisticas;
    }
    public function obtenerEstadisticasDelDia(int $idemp, string $fecha)
    {
        // Obtener todas las estadisticas del empleado para el dia especificado
        return [
            'asistencias' => $this->obtenerAsistenciasPorDia($idemp, $fecha),
            'ventas' => $this->obtenerVentasPorDia($idemp, $fecha),
            'recargas' => $this->obtenerGestionRecargasPorDia($idemp, $fecha),
            'productos' => $this->obtenerGestionProductosPorDia($idemp, $fecha),
            'inventario' => $this->obtenerGestionInventarioPorDia($idemp, $fecha),
            'cuentas' => $this->obtenerGestionCuentasPorDia($idemp, $fecha),
            'ventas' => $this->obtenerGestionVentasPorDia($idemp, $fecha),
            'tareas' => $this->obtenerGestionTareasPorDia($idemp, $fecha),
            'costos' => $this->obtenerGestionCostosPorDia($idemp, $fecha),
            'clientes' => $this->obtenerGestionClientesPorDia($idemp, $fecha),
            'gastos' => $this->obtenerGestionGastosPorDia($idemp, $fecha),
        ];
    }
    public function obtenerEstadisticasDeEmpleados($empleados, int $mes, int $anio)
    {
        $estadisticas = [];
        foreach ($empleados as $empleado) {
            $estadisticas[$empleado->idemp] = $this->obtenerEstadisticasDelMes($empleado->idemp, $empleado->nombreemp, $mes, $anio);
        }
        return $estadisticas;
    }
}
