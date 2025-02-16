<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

use App\Models\Historial;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico', 'vendedor', 'contador']);
        $cuentas = Cuenta::with(['valor'])->where('activocue', true)->orderBy('fechavencue')->get(); // Cargar valor asociado
        // Inicializar una colección vacía para los perfiles
        $perfiles = collect();

        $idcueSeleccionado = $request->idcue;

        //$usuariosActivos = ViewUsuarioActivo::where('IDCUE', $idcueSeleccionado)->get(); //por si acaso

        if ($idcueSeleccionado) {
            //$usuarioscuenta = Cuenta::where('idcue',$idcueSeleccionado)->get();
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
            foreach ($perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $idcueSeleccionado)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                ->where('fecha_vencimiento', '>', now()) // Solo usuarios con fecha_vencimiento mayor a hoy
                ->count();
            $cuenta->usuarios_activos = $usuarios;
        }

        // Pasar las cuentas y los perfiles a la vista
        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
    }

    // Mostrar formulario para crear una nueva cuenta contratada
    public function create()
    {

        $valores = Valor::all(); // Obtener lista de valores
        return view('inventory.cuentas.create', compact('valores'));
    }

    // Guardar una nueva cuenta
    public function store(Request $request)
    {
        try {
            $request->merge([
                'idcue' => strtoupper($request->idcue)
            ]);
            // Validar datos de la cuenta
            $validated = $request->validate([
                'idcue' => 'required|string|max:20|unique:cuentas,idcue',
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => 'required|string|max:50|unique:cuentas,idcue',
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean',
            ]);

            // Crear la cuenta (otra alternativa)
            $cuenta = Cuenta::create($validated);

            Historial::create([
                'accion' => 'Se creo la cuenta con ID: ' . $cuenta->idcue,
                'descripcion' =>  'Datos: ' . json_encode($cuenta), // Campo opcional
                'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),  // Almacena el nombre del usuario
                'fecha' => now(),
            ]);
            // Actualizar estado de productos relacionados con el servicio de la cuenta creada
            $this->actualizarEstadoProductos($cuenta->valor->idser);
            // Comprobar si los datos de costo están presentes
            // Si hay campos de costo, validarlos y crear el costo
            if ($request->filled('descripcioncos') || $request->filled('montocos')) {
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'required|string|max:50',
                    'montocos' => 'required|numeric|min:0',
                ]);

                // Crear el costo asociado a la cuenta
                $costo = Costo::create([
                    'idcue' => $request->idcue, // Asociar el costo a la cuenta recién creada
                    'fechacos' => now(),
                    'montocos' => $validatedCosto['montocos'],
                    'descripcioncos' => $validatedCosto['descripcioncos'],
                ]);

                Historial::create([
                    'accion' => 'Se creo el costo con ID: ' . $costo->idcos,
                    'descripcion' =>  'Datos: ' . json_encode($costo), // Campo opcional
                    'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),  // Almacena el nombre del usuario
                    'fecha' => now(),
                ]);
            }
            return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }
    // CuentaController.php
    public function status($idcue)
    {
        $cuenta = Cuenta::findOrFail($idcue);
        // Cambiar el estado de caidacue (de true a false o de false a true)
        $cuenta->caidacue = !$cuenta->caidacue; // Invertir el valor (true -> false o false -> true)
        // Guardar el cambio en la base de datos
        $cuenta->save();
        Historial::create([
            'accion' => 'Se actualizo el estado de cuenta con ID: ' . $cuenta->idcue,
            'descripcion' =>  'estado cambiado a ' . $cuenta->caidacue,
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),  // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        // Actualizar estado de productos relacionados con el servicio de la cuenta creada
        $this->actualizarEstadoProductos($cuenta->valor->idser);
        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }
    public function mensaje($perfilId)
    {
        // Obtener el perfil seleccionado
        $perfil = Perfil::find($perfilId);

        // Obtener la cuenta asociada al perfil
        $cuenta = Cuenta::where('idcue', $perfil->idcue)->first();

        // Obtener el valor asociado a la cuenta
        $valor = Valor::find($cuenta->idval);

        // Obtener el servicio asociado al valor
        $servicio = Servicio::find($valor->idser);

        // Construir el mensaje
        $mensaje = "<strong>{$servicio->nombre}</strong>\n";
        $mensaje .= "Usuario: {$cuenta->usuariocue}\n";
        $mensaje .= "Clave: {$cuenta->contrasenacue}\n";
        $mensaje .= "PIN de perfil {$perfil->numeroper}: ";
        $mensaje .= "{$perfil->pinper}\n";

        Historial::create([
            'accion' => 'Se solicito los datos de perfil' . $perfil->numeroper . ' de la cuenta: ' . $cuenta->idcue,
            'descripcion' =>  null, // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        // Devolver el mensaje al frontend
        return response()->json(['mensaje' => $mensaje]);
    }

    // Mostrar formulario para editar una cuenta
    public function edit($idcue)
    {
        // Buscar la cuenta con la relacion valores
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::all();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        // Buscar la cuenta con la relacion valores
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valor = $cuenta->idval;
        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
    }

    // Actualizar una cuenta existente
    public function update(Request $request, $idcue)
    {
        try {
            $request->validate([
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => 'required|string|max:50',
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean|min:1'
            ]);
            $request->merge([
                'idcue' => strtoupper($request->idcue)
            ]);

            $cuenta = Cuenta::findOrFail($idcue);

            Historial::create([
                'accion' => 'Actualización de Cuenta',
                'descripcion' =>  'Datos antiguos: ' . json_encode($cuenta), // Campo opcional
                'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
                'fecha' => now(),
            ]);

            $cuenta->update($request->all());

            // Actualizar estado de productos relacionados con el servicio de la cuenta creada
            $this->actualizarEstadoProductos($cuenta->valor->idser);
            if (!empty($request->descripcioncos) && !empty($request->montocos)) {
                // Validar datos de costo si los campos están presentes
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'string|max:50',
                    'montocos' => 'numeric|min:0',
                ]);

                // Crear el costo asociado a la cuenta solo si los datos de costo están presentes
                Costo::create([
                    'idcue' => $cuenta->idcue,
                    'descripcioncos' => $request->descripcioncos,
                    'montocos' => $request->montocos,
                    'fechacos' => now(),  // O la fecha que desees
                ]);
            }
            return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    // Eliminar una cuenta
    public function destroy($idcue)
    {
        $cuenta = Cuenta::findOrFail($idcue);
        // Verificar si los perfiles están registrados en detalles_venta
        $cuentaInUsuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->exists();

        if ($cuentaInUsuariosActivos) {
            return redirect()->route('cuentas')->with('error', 'No se puede eliminar la cuenta porque uno o más clientes aun la usan');
        }
        Historial::create([
            'accion' => 'Se desactivó la cuenta con ID: ' . $cuenta->idcue,
            'descripcion' =>  'Datos inactivos: ' . json_encode($cuenta), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        // Obtener el nuevo ID con secuencia
        $nuevoId = $this->generarNuevoId($cuenta->idcue);

        // Obtener perfiles asociados a la cuenta
        $perfiles = Perfil::where('idcue', $cuenta->idcue)->get();

        foreach ($perfiles as $perfil) {
            // Generar un nuevo ID de perfil con "_borradaX"
            $nuevoIdPer = $this->generarNuevoIdPerfil($perfil->idper);

            // Actualizar el ID del perfil
            $perfil->update([
                'idper' => $nuevoIdPer
            ]);
        }

        $cuenta->update([
            'activocue' => false,
            'idcue' => $nuevoId
        ]);
        // Actualizar estado de productos relacionados con el servicio de la cuenta creada
        $this->actualizarEstadoProductos($cuenta->valor->idser);
        return redirect()->route('cuentas')->with('success', 'Cuenta desactivada con éxito.');
    }
    private function authorizeRole(array $roles)
    {
        $userRole = Auth::user()->idrol;

        if (!in_array($userRole, $roles)) {
            // Redirigir a la vista anterior con una alerta
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }
    private function actualizarEstadoProductos($idser)
    {
        // Buscar productos individuales (tipo_producto_id = 1) con el servicio específico
        $productos = Producto::where('tipo_producto_id', 1)
            ->whereHas('detalles', function ($query) use ($idser) {
                $query->where('idser', $idser);
            })->get();

        foreach ($productos as $producto) {
            // Verificar si hay cuentas disponibles para el servicio del producto
            $cuentaDisponible = $this->buscarCuentaDisponible($idser);

            // Si hay cuenta disponible, activar el producto; si no, desactivarlo
            $producto->update(['activo' => $cuentaDisponible ? true : false]);
        }
    }
    private function buscarCuentaDisponible($idser)
    {
        return Cuenta::whereHas('valor', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })
            ->where('caidacue', false)
            ->where('activocue', true)
            ->whereHas('valor', function ($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = cuentas.idcue) < valores.pantmaxval');
            })
            ->first();
    }
    private function generarNuevoId($idcue)
    {
        // Buscar el último número de secuencia usado
        $baseId = preg_replace('/_borrada\d*$/', '', $idcue); // Remueve _borradaX si ya existe
        $contador = 1;

        // Buscar el último idcue que coincida con el patrón
        $ultimoId = Cuenta::where('idcue', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idcue) DESC") // Ordena por longitud para evitar desorden (_borrada10 antes de _borrada2)
            ->orderBy('idcue', 'DESC') // Ordena numéricamente
            ->pluck('idcue')
            ->first();

        // Si hay un último ID con secuencia, extraer el número
        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1; // Incrementar la secuencia
            }
        }

        return "{$baseId}_borrada{$contador}";
    }
    private function generarNuevoIdPerfil($idper)
    {
        $baseId = preg_replace('/_borrada\d*$/', '', $idper);
        $contador = 1;

        $ultimoId = Perfil::where('idper', 'LIKE', "{$baseId}_borrada%")
            ->orderByRaw("LENGTH(idper) DESC")
            ->orderBy('idper', 'DESC')
            ->pluck('idper')
            ->first();

        if ($ultimoId) {
            preg_match('/_borrada(\d+)$/', $ultimoId, $matches);
            if (!empty($matches[1])) {
                $contador = (int) $matches[1] + 1;
            }
        }

        return "{$baseId}_borrada{$contador}";
    }
}
