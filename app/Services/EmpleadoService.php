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
        // Obtener todas las asistencias del empleado para el día, mes y año especificados
        return Asistencia::where('empleado_id', $idemp)
            ->whereDate('created_at', $fecha)
            ->get();
    }
    public function obtenerLapsosDeAsistenciasPorDia(int $idemp, string $fecha)
    {
        $asistencias = $this->obtenerAsistenciasPorDia($idemp, $fecha);
        $lapsos = [];
        $total_conexion = 0;

        if (count($asistencias) === 0) {
            return [
                'lapsos' => [],
                'total_conexion' => 0
            ];
        }

        $inicio = $asistencias[0]->created_at;
        $fin = $asistencias[0]->created_at;
        $tiempo_conexion = 0;

        for ($i = 1; $i < count($asistencias); $i++) {
            $anterior = Carbon::parse($asistencias[$i - 1]->created_at);
            $actual = Carbon::parse($asistencias[$i]->created_at); // 👈 AQUI

            if ($actual->diffInMinutes($anterior) <= 5) {
                $fin = $actual;
                $tiempo_conexion += 5;
            } else {
                $lapsos[] = [
                    'inicio' => $inicio,
                    'fin' => $fin,
                    'tiempo_conexion' => $tiempo_conexion,
                ];
                $total_conexion += $tiempo_conexion;

                // Iniciar nuevo lapso
                $inicio = $actual;
                $fin = $actual;
                $tiempo_conexion = 0;
            }
        }

        // Agregar último lapso
        $lapsos[] = [
            'inicio' => $inicio,
            'fin' => $fin,
            'tiempo_conexion' => $tiempo_conexion,
        ];
        $total_conexion += $tiempo_conexion;

        return [
            'lapsos' => $lapsos,
            'total_conexion' => $total_conexion
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
        // Contar todas las asistencias del empleado para el día, mes y año especificados
        return Historial::where('empleado_id', $idemp)
            ->where(function ($query) {
                $query->where('accion', 'like', '%tarea%')
                    ->orWhere('accion', 'like', '%Tarea%');
            }) // Buscar texto que contenga "Tarea"
            ->whereDate('created_at', $fecha)
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
                $totalConexion = $this->obtenerLapsosDeAsistenciasPorDia($idemp, $fecha)['total_conexion'];

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
