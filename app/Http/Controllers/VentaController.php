<?php

namespace App\Http\Controllers;

use App\Mail\facturaMail;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Cuenta;
use App\Models\Recarga;
use App\Models\Pedido;
use App\Models\Historial;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->authorizeRole(['administrador', 'vendedor']);

        $ventas = Venta::with(['detalles_venta'])->orderBy('created_at', 'desc')->get();

        $hoy = Carbon::today(); // Fecha del inicio del día
        $ingresos_dia = Venta::whereDate('fechaven', $hoy)->sum('totalpagoven');
        $ventas_dia = Venta::whereDate('fechaven', $hoy)->count();

        $autenticados = Cliente::whereNotNull('email')
            ->whereNotNull('password')
            ->count();

        $recargasPendientes = Recarga::where('idestado', 1)->count();
        $pedidosPendientes = Pedido::where('idestado', 1)->count();
        $ventasLaravel = Venta::whereDate('fechaven', $hoy)->where('idemp', 10)->count();
        // Pasar las ventas y los detalles de venta a la vista
        return view('sales.ventas.index', compact('ventas', 'ingresos_dia', 'ventas_dia', 
            'autenticados', 'recargasPendientes', 'pedidosPendientes', 'ventasLaravel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $this->authorizeRole(['administrador', 'vendedor']);
        $clientes = Cliente::all(); // Obtener lista de clientes
        $empleados = Empleado::all(); //Obtener lista de empleados
        // Obtener las cuentas con sus perfiles
        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        return view('sales.ventas.create', compact('empleados', 'clientes', 'cuentas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // Guardar una nueva venta
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);
        // Decodificar los detalles de venta desde el JSON
        $detalles = json_decode($request->detalles_venta, true);

        // Calcular el total de la venta sumando los montos de los detalles
        $total_venta = collect($detalles)->sum('monto');

        $fecha = Carbon::today()->toDateString();  // Solo la fecha sin la hora


        // Crear la venta
        $venta = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,  // Puedes calcular el total si lo deseas
        ]);

        $venta->idven = DB::table('ventas')->where('idcli', $request->idcli)
            ->where('idemp', $request->idemp)
            ->where('fechaven', $fecha)
            ->orderBy('idven', 'desc')
            ->value('idven');

        //$venta->refresh(); //linea importante que recupera el idven
        //dd($venta); verificar que la variable tenga los valores correctos
        // Registrar los detalles de venta
        $descripcionDetalles = ""; // Para construir la descripción del historial
        $totalDetalles = count($detalles); // Contar la cantidad total de detalles
        $totalVenta = 0.00;
        foreach ($detalles as $detalle) {
            // Obtener el idcue de la cuenta
            $idcue = $detalle['cuenta'];

            // Aquí dividimos el perfil para obtener el número del perfil
            $numeroper = $detalle['perfil']; // Asumiendo que 'perfil' es algo como '1.5'

            // Concatenar el idcue y el numeroper para obtener el idperfil
            $idper = $idcue . '.' . $numeroper;
            //dd($detalle['descripcion']);
            // Guardar cada detalle en la tabla detalles_venta
            $detalleRec = DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);

            // Agregar el monto al total de la venta
            $totalVenta += $detalleRec->montodet;
            $descripcionDetalles .= "Cuenta: {$idcue}, Perfil: {$numeroper}, Monto: {$detalleRec->montodet}; ";
        }
        // Agregar el total de detalles a la descripción
        $descripcionDetalles .= "Cuentas vendidas: {$totalDetalles}. Total de la venta: {$totalVenta}.";

        Historial::create([
            'accion' => 'Venta-Realizada Factura: ' . $venta->idven,
            'descripcion' =>  'Datos: ' . json_encode($venta). 'Detalles: '. $descripcionDetalles,
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        // Puedes calcular el total de la venta aquí y actualizarlo
        //$venta->totalpagoven = collect($detalles)->sum('monto');
        //$venta->save();

        // Redirigir a una página de éxito o mostrar un mensaje
        return redirect()->route('ventas')->with('success', 'Venta registrada correctamente');
    }

    public function storeRenew(Request $request)
    {
        // Validación
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);
        // Obtener el idven anterior
        $idvenPasado = $request->idvenPasado;

        DetalleVenta::where('idven', $idvenPasado)->update(['activodet' => false]);

        // Decodificar los detalles de venta desde el JSON
        $detalles = json_decode($request->detalles_venta, true);

        // Calcular el total de la venta sumando los montos de los detalles
        $total_venta = collect($detalles)->sum('monto');
        $fecha = Carbon::today()->toDateString();
        // Crear la nueva venta
        $ventaNueva = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,  // Calcula el total si es necesario
        ]);
        $ventaNueva->idven = DB::table('ventas')->where('idcli', $request->idcli)
            ->where('idemp', $request->idemp)
            ->where('fechaven', $fecha)
            ->orderBy('idven', 'desc')
            ->value('idven');

        Historial::create([
            'accion' => 'Renovación-Venta ' . $idvenPasado,
            'descripcion' =>  'Nueva venta creada: ' . json_encode($ventaNueva), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        // Registrar los detalles de venta
        foreach ($detalles as $detalle) {
            // Obtener el idcue de la cuenta
            $idcue = $detalle['cuenta'];

            // Obtener el número de perfil
            $numeroper = $detalle['perfil'];

            // Crear el idperfil combinando idcue y numeroper
            $idper = $idcue . '.' . $numeroper;

            // Guardar cada detalle en la tabla detalles_venta
            $detalle2 = DetalleVenta::create([
                'idven' => $ventaNueva->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);
        }

        // Redirigir a una página de éxito o mostrar un mensaje
        return redirect()->route('ventas')->with('success', 'Venta registrada correctamente.');
    }

    public function storeCliente(Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:15|unique:clientes,telefonocli'
        ]);

        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();

        // Si el cliente ya existe, redirigir con mensaje de error
        if ($clienteExistente) {
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        } else {

            $cliente = Cliente::create($request->all());

            Historial::create([
                'accion' => 'Creación de Cliente en Ventas',
                'descripcion' =>  'Datos: ' . json_encode($cliente), // Campo opcional
                'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
                'fecha' => now(),
            ]);
            // Retornar el cliente recién creado como respuesta
            //return response()->json(['cliente' => $cliente]);
            return redirect()->route('ventas.create')->with('success', 'Cliente creado con éxito.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($idven)
    {

        $this->authorizeRole(['administrador', 'vendedor']);
        $venta = Venta::with(['detalles_venta'])->findOrFail($idven);
        $empleados = Empleado::all();

        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        return view('sales.ventas.edit', compact('venta', 'empleados', 'cuentas'));
    }

    public function renew($idcli, $idven)
    {
        $venta = Venta::with('detalles_venta', 'cliente')->findOrFail($idven);

        $empleados = Empleado::all(); //Obtener lista de empleados
        // Obtener las cuentas con sus perfiles
        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

        if ($venta->idcli != $idcli) {
            abort(404, 'Cliente no coincide con la venta.');
        }

        $cuentas = Cuenta::with('perfiles')->get();

        $detalles = $venta->detalles_venta->map(function ($detalle) {
            $detalle->fechavendet_suma = Carbon::parse($detalle->fechavendet)->addMonth()->format('Y-m-d');
            return $detalle;
        });

        $totalVenta = $venta->detalles_venta->sum('montodet'); // Calcular total de la venta

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }

        return view('sales.ventas.renew', [
            'empleados' => $empleados,
            'cuentas' => $cuentas,
            'venta' => $venta,
            'detalles' => $detalles,
            'totalVenta' => $totalVenta
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idven)
    {
        // Validación
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        // Obtener la venta existente
        $venta = Venta::findOrFail($idven);

        // Comprobamos que el cliente no cambie, ya que no es editable
        if ($venta->idcli != $request->idcli) {
            return redirect()->route('ventas.edit', $idven)->with('error', 'El cliente no puede modificarse.');
        }

        // Actualizamos la venta (sin modificar el cliente)
        //$venta->idemp = $request->idemp;
        //$venta->fechaven = Carbon::now();  // Actualizamos la fecha si es necesario
        $venta->totalpagoven = 0;  // Lo inicializamos a 0 para el nuevo cálculo
        $venta->save();

        // Decodificar los detalles de venta desde el JSON
        $detalles = json_decode($request->detalles_venta, true);

        // Primero, eliminamos los detalles anteriores
        $venta->detalles_venta()->delete();

        // Registrar los nuevos detalles de venta
        $totalVenta = 0;
        foreach ($detalles as $detalle) {
            // Obtener el idcue de la cuenta
            $idcue = $detalle['cuenta'];

            // Aquí dividimos el perfil para obtener el número del perfil
            $numeroper = $detalle['perfil']; // Asumiendo que 'perfil' es algo como '1.5'

            // Concatenar el idcue y el numeroper para obtener el idperfil
            $idper = $idcue . '.' . $numeroper;

            // Verificar si el estado es 'Activa' o 'Vencida' y convertirlo en booleano
            //$estado = $detalle['estado'] == 'Activa' ? true : false; // Activa = true, Vencida = false

            // Guardar cada detalle en la tabla detalles_venta
            DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => $detalle['estado'],
            ]);
            // Acumulamos el total de la venta
            $totalVenta += $detalle['monto'];
        }

        // Actualizamos el total de la venta
        $venta->totalpagoven = $totalVenta;
        $venta->save();

        Historial::create([
            'accion' => 'Actualización de venta ' . $venta->idven,
            'descripcion' =>  'Datos: '.json_encode($venta), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        // Redirigir a una página de éxito o mostrar un mensaje
        return redirect()->route('ventas')->with('success', 'Venta actualizada correctamente');
    }

    public function status($iddet)
    {
        $detalle = DetalleVenta::findOrFail($iddet);
        // Cambiar el estado de activodet (de true a false o de false a true)
        $detalle->activodet = !$detalle->activodet; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $detalle->save();

        Historial::create([
            'accion' => 'Estado de usuario actualizado' . $iddet,
            'descripcion' =>  'Estado cambiado a' . $detalle->activodet, // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('ventas')->with('success', 'Estado de la cuenta del cliente actualizado correctamente.');
    }
    public function sendInvoice($id)
    {
        $venta = Venta::findOrFail($id);
        $cliente = $venta->cliente;

        // Lógica para generar y enviar la factura por correo
        Mail::to($cliente->email)->send(new facturaMail($venta));

        return redirect()->route('ventas')->with('success', 'Factura enviada correctamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($idven)
    {
        $venta = Venta::findOrFail($idven);

        Historial::create([
            'accion' => 'Eliminación de Venta',
            'descripcion' =>  'Datos Eliminados: ' . json_encode($venta), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $venta->detalles_venta()->delete();
        $venta->delete();

        return redirect()->route('ventas')->with('success', 'Venta eliminada con éxito.');
    }
    public function indexApi(Request $request)
    {
        // Obtener todas las ventas con los detalles de cada una
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven', 'desc')->get();

        // Calcular los ingresos y ventas del día
        $hoy = Carbon::today(); // Fecha del inicio del día
        $ingresos_dia = Venta::whereDate('fechaven', $hoy)->sum('totalpagoven');
        $ventas_dia = Venta::whereDate('fechaven', $hoy)->count();

        // Retornar las ventas y los ingresos como una respuesta JSON
        return response()->json([
            'ventas' => $ventas,
            'ingresos_dia' => $ingresos_dia,
            'ventas_dia' => $ventas_dia,
        ]);
    }

    private function authorizeRole(array $roles)
    {
        $userRole = Auth::user()->idrol;

        if (!in_array($userRole, $roles)) {
            // Redirigir a la vista anterior con una alerta
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }
}
