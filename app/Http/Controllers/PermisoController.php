<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{
    /**
     * Muestra una lista de los permisos.
     */
    public function index()
    {
        $permisos = Permiso::with('rol')->get(); // Cargar los permisos con el rol asociado
        return view('permisos.index', compact('permisos'));
    }

    /**
     * Muestra el formulario para crear un nuevo permiso.
     */
    public function create()
    {
        $roles = Rol::all(); // Obtener todos los roles disponibles
        return view('permisos.create', compact('roles'));
    }

    /**
     * Almacena un nuevo permiso en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idrol' => 'required|exists:roles,idrol',
            'name_table' => 'required|string|max:50',
            'accion' => 'required|string|max:50',
            'allowed' => 'required|boolean',
        ]);

        // Verificar si el permiso ya existe
        $exists = Permiso::where('idrol', $request->idrol)
            ->where('name_table', $request->name_table)
            ->where('accion', $request->accion)
            ->exists();

        // Si el permiso ya existe, redirigir con un mensaje de error
        if ($exists) {
            return redirect()->route('permisos.create')
                ->with('error', 'Este permiso ya ha sido registrado.');
        }
        // Crear el permiso en la base de datos
        Permiso::create($request->all());

        // Obtener los datos necesarios para el GRANT
        $accion = $request->accion;
        $name_table = $request->name_table;
        $idrol = $request->idrol;

        // Validar y escapar los valores para evitar inyecciones SQL
        $accion = preg_replace('/[^a-zA-Z0-9_]/', '', $accion);  // Solo caracteres alfanuméricos y guion bajo
        $name_table = preg_replace('/[^a-zA-Z0-9_]/', '', $name_table);  // Solo caracteres alfanuméricos y guion bajo

        // Verificar si se debe conceder el permiso
        if ($request->allowed) {
            // Asegurarse de que el idrol es mayor que 0
            if ($idrol <= 0) {
                return redirect()->route('permisos.create')
                    ->with('error', 'El rol seleccionado no es válido.');
            }

            // Construir la consulta SQL de forma segura con interpolación
            $sql = "GRANT {$accion} ON {$name_table} TO {$idrol}";

            // Ejecutar la consulta SQL cruda
            DB::statement($sql);
        }
        //DB::statement("GRANT $accion ON $name_table TO $idrol");
        return redirect()->route('permisos')->with('success', 'Permiso creado exitosamente.');
    }

    /**
     * Muestra un permiso específico.
     */
    public function show($id)
    {
        $permiso = Permiso::with('rol')->findOrFail($id);
        return view('permisos.show', compact('permiso'));
    }

    /**
     * Muestra el formulario para editar un permiso existente.
     */
    public function edit($id)
    {
        $permiso = Permiso::findOrFail($id);
        $roles = Rol::all();
        return view('permisos.edit', compact('permiso', 'roles'));
    }

    /**
     * Actualiza un permiso existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'idrol' => 'required|exists:roles,idrol',
            'name_table' => 'required|string|max:50',
            'accion' => 'required|string|max:50',
            'allowed' => 'required|boolean',
        ]);

        $permiso = Permiso::findOrFail($id);
        $oldUserRole = $permiso['idrol'];
        $oldNameTable = $permiso['name_table'];
        $oldAccion = $permiso['accion'];

        $idrol = $request->idrol;
        $accion = $request->accion;
        $name_table = $request->name_table;
        $allowed = $request->allowed;

        // Revocar el permiso actual antes de actualizar
        $revokeSql = "REVOKE $oldAccion ON $oldNameTable FROM $oldUserRole";
        DB::statement($revokeSql);

        // 3. Otorgar el nuevo permiso si está permitido
        if ($allowed) {
            $grantSql = "GRANT $accion ON $name_table TO $idrol";
            // Ejecutar la consulta SQL cruda
            DB::statement($grantSql);
        }
        $permiso->update($request->all());
        return redirect()->route('permisos')->with('success', 'Permiso actualizado exitosamente.');
    }

    /**
     * Elimina un permiso de la base de datos.
     */
    public function destroy($id)
    {
        $permiso = Permiso::findOrFail($id);
        
        
        $idrol = $permiso['idrol'];
        $nameTable = $permiso['name_table'];
        $accion = $permiso['accion'];
        // Eliminar el permiso de la tabla user_permissions

        // Ejecutamos la consulta REVOKE
        $sql2 = "REVOKE $accion ON $nameTable FROM $idrol";
        
        DB::statement($sql2);
        $permiso->delete();
        return redirect()->route('permisos')->with('success', 'Permiso eliminado exitosamente.');
    }
}
