<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'Admin']);
        $bodeguero = Role::create(['name' => 'Bodeguero']);
        $tecnico = Role::create(['name' => 'Tecnico']);
        $contador = Role::create(['name' => 'Contador']);
        $vendedor = Role::create(['name' => 'Vendedor']);
        $trabajador = Role::create(['name' => 'Trabajador']);
        $gerente = Role::create(['name' => 'Gerente']);
        $visitante = Role::create(['name' => 'Visitante']);

        //$empleado = Empleado::where('usuarioemp', 'pablinmind')->first();
        //$empleado->assignRole('Admin');

        Permission::create(['name' => 'historial'])->syncRoles([$admin, $gerente, $visitante, $trabajador]);
        Permission::create(['name' => 'tareas.destroy'])->syncRoles([$admin, $gerente, $tecnico]);
        Permission::create(['name' => 'dashboard'])->syncRoles([$admin, $gerente, $visitante, $contador]);
        Permission::create(['name' => 'dashboard.store'])->syncRoles([$admin, $gerente, $contador]);

        Permission::create(['name' => 'costos'])->syncRoles([$admin, $gerente, $visitante, $trabajador, $contador, $bodeguero]);
        Permission::create(['name' => 'costos.store'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'costos.update'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'costos.destroy'])->syncRoles([$admin, $gerente, $contador, $bodeguero]);

        Permission::create(['name' => 'gastos'])->syncRoles([$admin, $gerente, $visitante, $trabajador, $contador, $bodeguero]);
        Permission::create(['name' => 'gastos.store'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'gastos.update'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'gastos.destroy'])->syncRoles([$admin, $gerente, $contador, $bodeguero]);

        Permission::create(['name' => 'tipos.store'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'tipos.update'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador]);
        Permission::create(['name' => 'tipos.destroy'])->syncRoles([$admin, $gerente]);

        Permission::create(['name' => 'servicios'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'servicios.create'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'servicios.store'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        Permission::create(['name' => 'servicios.edit'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'servicios.update'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        Permission::create(['name' => 'servicios.destroy'])->syncRoles([$admin, $gerente, $bodeguero]);

        Permission::create(['name' => 'valores'])->syncRoles([$admin, $gerente, $contador, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'valores.create'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'valores.store'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico]);
        Permission::create(['name' => 'valores.edit'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'valores.update'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        Permission::create(['name' => 'valores.destroy'])->syncRoles([$admin, $gerente, $bodeguero]);

        Permission::create(['name' => 'proveedores'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'proveedores.create'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'proveedores.store'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico]);
        Permission::create(['name' => 'proveedores.edit'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'proveedores.update'])->syncRoles([$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico]);
        Permission::create(['name' => 'proveedores.destroy'])->syncRoles([$admin, $gerente, $bodeguero]);

        Permission::create(['name' => 'clientes'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'clientes.create'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'clientes.store'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'clientes.storeInVenta'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'clientes.edit'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'clientes.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'clientes.destroy'])->syncRoles([$admin, $gerente]);

        Permission::create(['name' => 'cuentas'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $contador, $tecnico, $visitante]);
        Permission::create(['name' => 'cuentas.create'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $visitante]);
        Permission::create(['name' => 'cuentas.store'])->syncRoles([$admin, $gerente, $trabajador, $tecnico]);
        Permission::create(['name' => 'cuentas.status'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico]);
        Permission::create(['name' => 'cuentas.mensaje'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        Permission::create(['name' => 'cuentas.edit'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'cuentas.renew'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $contador]);
        Permission::create(['name' => 'cuentas.update'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $contador]);
        Permission::create(['name' => 'cuentas.destroy'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero]);
        Permission::create(['name' => 'perfil.update'])->syncRoles([$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico]);

        Permission::create(['name' => 'ventas'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'ventas.create'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'ventas.store'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'ventas.storeRenew'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'ventas.storeCliente'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'ventas.status'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'ventas.edit'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'ventas.renew'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'ventas.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        Permission::create(['name' => 'ventas.destroy'])->syncRoles([$admin, $gerente]);
        Permission::create(['name' => 'ventas.sendInvoice'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);
        
        Permission::create(['name' => 'usuarios'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'usuarios.change'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'usuarios.renew'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante]);
        Permission::create(['name' => 'usuarios.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        Permission::create(['name' => 'usuarios.destroy'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $tecnico]);

        Permission::create(['name' => 'empleados'])->syncRoles([$admin, $gerente, $visitante]);
        Permission::create(['name' => 'empleados.create'])->syncRoles([$admin, $gerente, $visitante]);
        Permission::create(['name' => 'empleados.store'])->syncRoles([$admin, $gerente]);
        Permission::create(['name' => 'empleados.edit'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $contador, $tecnico, $visitante]);
        Permission::create(['name' => 'empleados.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $contador, $tecnico]);
        Permission::create(['name' => 'empleados.updateRol'])->syncRoles([$admin, $gerente]);
        Permission::create(['name' => 'empleados.destroy'])->syncRoles([$admin]);

        Permission::create(['name' => 'mantenimientos'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        Permission::create(['name' => 'mantenimientos.create'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        Permission::create(['name' => 'mantenimientos.store'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        Permission::create(['name' => 'mantenimientos.edit'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        Permission::create(['name' => 'mantenimientos.update'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        Permission::create(['name' => 'mantenimientos.destroy'])->syncRoles([$admin, $gerente, $tecnico, $trabajador, $bodeguero]);

        Permission::create(['name' => 'gestion'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        Permission::create(['name' => 'categorias.store'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'categorias.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'categorias.destroy'])->syncRoles([$admin, $gerente]);
        Permission::create(['name' => 'tipos_producto.store'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'tipos_producto.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'tipos_producto.destroy'])->syncRoles([$admin, $gerente]);

        Permission::create(['name' => 'productos.index'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        Permission::create(['name' => 'productos.create'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        Permission::create(['name' => 'productos.store'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'productos.show'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        Permission::create(['name' => 'productos.edit'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        Permission::create(['name' => 'productos.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        Permission::create(['name' => 'productos.destroy'])->syncRoles([$admin, $gerente]);

        Permission::create(['name' => 'empleado.recargas.index'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'empleado.recargas.updateEstado'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);

        Permission::create(['name' => 'empleado.pedidos.index'])->syncRoles([$admin, $gerente, $trabajador, $vendedor, $visitante]);
        Permission::create(['name' => 'empleado.pedidos.update'])->syncRoles([$admin, $gerente, $trabajador, $vendedor]);

        Permission::create(['name' => 'roles.index'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.store'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.update'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.destroy'])->syncRoles([$admin]);

        Permission::create(['name' => 'historial.clear'])->syncRoles([$admin]);
        Permission::create(['name' => 'tareas.index'])->syncRoles([$admin]);
    }
}
