<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verifica manualmente el permiso para ver roles
        if (!Gate::allows('roles.index')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Gate::allows('roles.store')) {
            abort(403, 'No tienes permiso para crear roles.');
        }
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('roles.store')) {
            abort(403, 'No tienes permiso para crear roles.');
        }
        $request->validate([
            'name' => 'required',
        ]);

        $role = Role::create($request->only('name'));
        $role->permissions()->sync($request->permissions);

        return redirect()->route('roles.edit', $role)->with('success', 'El rol se creó correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Puedes agregar verificación de permisos si lo requieres.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!Gate::allows('roles.update')) {
            abort(403, 'No tienes permiso para editar roles.');
        }
        $rol = Role::findOrFail($id);
        $permissions = Permission::all();
        return view('roles.edit', compact('permissions', 'rol'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Gate::allows('roles.update')) {
            abort(403, 'No tienes permiso para actualizar roles.');
        }
        $request->validate([
            'name' => 'required',
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->only('name'));
        $role->permissions()->sync($request->permissions);

        return redirect()->route('roles.edit', $role)->with('success', 'El rol se actualizó correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Gate::allows('roles.destroy')) {
            abort(403, 'No tienes permiso para eliminar roles.');
        }
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'El rol se eliminó correctamente');
    }
}