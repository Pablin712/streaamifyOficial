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
    private function syncRole(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    private function syncPermission(string $name, array $roles = []): Permission
    {
        $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

        if (! empty($roles)) {
            $permission->syncRoles($roles);
        }

        return $permission;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin             = $this->syncRole('Admin');
        $bodeguero         = $this->syncRole('Bodeguero');
        $tecnico           = $this->syncRole('Tecnico');
        $contador          = $this->syncRole('Contador');
        $vendedor          = $this->syncRole('Vendedor');
        $trabajador        = $this->syncRole('Trabajador');
        $gerente           = $this->syncRole('Gerente');
        $visitante         = $this->syncRole('Visitante');
        $trabajadorExterno = $this->syncRole('Trabajador externo');

        //$empleado = Empleado::where('usuarioemp', 'pablinmind')->first();
        //$empleado->assignRole('Admin');

        $this->syncPermission('historial', [$admin, $gerente, $visitante, $trabajador, $trabajadorExterno]);
        $this->syncPermission('tareas.destroy', [$admin, $gerente, $tecnico]);
        $this->syncPermission('dashboard', [$admin, $gerente, $visitante, $contador]);
        $this->syncPermission('dashboard.store', [$admin, $gerente, $contador]);

        $this->syncPermission('costos', [$admin, $gerente, $visitante, $trabajador, $contador, $bodeguero]);
        $this->syncPermission('costos.store', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('costos.update', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('costos.destroy', [$admin, $gerente, $contador, $bodeguero]);

        $this->syncPermission('gastos', [$admin, $gerente, $visitante, $trabajador, $contador, $bodeguero]);
        $this->syncPermission('gastos.store', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('gastos.update', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('gastos.destroy', [$admin, $gerente, $contador, $bodeguero]);

        $this->syncPermission('tipos.store', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('tipos.update', [$admin, $gerente, $contador, $bodeguero, $trabajador]);
        $this->syncPermission('tipos.destroy', [$admin, $gerente]);

        $this->syncPermission('servicios', [$admin, $gerente, $contador, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('servicios.create', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('servicios.store', [$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        $this->syncPermission('servicios.edit', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('servicios.update', [$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        $this->syncPermission('servicios.destroy', [$admin, $gerente, $bodeguero]);

        $this->syncPermission('valores', [$admin, $gerente, $contador, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('valores.create', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('valores.store', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $trabajadorExterno]);
        $this->syncPermission('valores.edit', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('valores.update', [$admin, $gerente, $bodeguero, $trabajador, $vendedor]);
        $this->syncPermission('valores.destroy', [$admin, $gerente, $bodeguero]);

        $this->syncPermission('proveedores', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        $this->syncPermission('proveedores.create', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        $this->syncPermission('proveedores.store', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('proveedores.edit', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico, $visitante]);
        $this->syncPermission('proveedores.update', [$admin, $gerente, $bodeguero, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('proveedores.destroy', [$admin, $gerente, $bodeguero]);

        $this->syncPermission('clientes', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('clientes.create', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('clientes.store', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('clientes.storeInVenta', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('clientes.edit', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('clientes.update', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('clientes.destroy', [$admin, $gerente]);

        $this->syncPermission('cuentas', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $contador, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('cuentas.create', [$admin, $gerente, $trabajador, $bodeguero, $visitante, $trabajadorExterno]);
        $this->syncPermission('cuentas.store', [$admin, $gerente, $trabajador, $tecnico, $trabajadorExterno]);
        $this->syncPermission('cuentas.status', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $trabajadorExterno]);
        $this->syncPermission('cuentas.mensaje', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $trabajadorExterno]);
        $this->syncPermission('cuentas.edit', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('cuentas.renew', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $contador, $trabajadorExterno]);
        $this->syncPermission('cuentas.update', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $contador, $trabajadorExterno]);
        $this->syncPermission('cuentas.destroy', [$admin, $gerente, $trabajador, $bodeguero]);
        $this->syncPermission('perfil.update', [$admin, $gerente, $trabajador, $bodeguero, $vendedor, $tecnico, $trabajadorExterno]);

        $this->syncPermission('ventas', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('ventas.create', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('ventas.store', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('ventas.storeRenew', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('ventas.storeCliente', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('ventas.status', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('ventas.edit', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('ventas.renew', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('ventas.update', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('ventas.destroy', [$admin, $gerente]);
        $this->syncPermission('ventas.sendInvoice', [$admin, $gerente, $trabajador, $vendedor]);

        $this->syncPermission('usuarios', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('usuarios.change', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('usuarios.renew', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $visitante, $trabajadorExterno]);
        $this->syncPermission('usuarios.update', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $trabajadorExterno]);
        $this->syncPermission('usuarios.destroy', [$admin, $gerente, $trabajador, $vendedor, $tecnico, $trabajadorExterno]);

        $this->syncPermission('empleados', [$admin, $gerente, $visitante]);
        $this->syncPermission('empleados.create', [$admin, $gerente, $visitante]);
        $this->syncPermission('empleados.store', [$admin, $gerente]);
        $this->syncPermission('empleados.edit', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $contador, $tecnico, $visitante]);
        $this->syncPermission('empleados.update', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $contador, $tecnico]);
        $this->syncPermission('empleados.updateRol', [$admin, $gerente]);
        $this->syncPermission('empleados.destroy', [$admin]);

        $this->syncPermission('mantenimientos', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        $this->syncPermission('mantenimientos.create', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        $this->syncPermission('mantenimientos.store', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        $this->syncPermission('mantenimientos.edit', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        $this->syncPermission('mantenimientos.update', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);
        $this->syncPermission('mantenimientos.destroy', [$admin, $gerente, $tecnico, $trabajador, $bodeguero]);

        $this->syncPermission('soportes', [$admin, $gerente, $tecnico, $trabajadorExterno]);
        $this->syncPermission('soportes.update', [$admin, $gerente, $tecnico, $trabajadorExterno]);

        // Permisos de chat (consolidados aquí para todos los roles relevantes)
        $this->syncPermission('chat.ver', [$admin, $gerente, $trabajador, $tecnico, $vendedor, $trabajadorExterno]);
        $this->syncPermission('chat.responder', [$admin, $gerente, $trabajador, $tecnico, $vendedor, $trabajadorExterno]);
        $this->syncPermission('chat.cerrar', [$admin, $gerente, $trabajador, $tecnico, $trabajadorExterno]);
        $this->syncPermission('chat.transferir', [$admin, $gerente, $trabajador]);
        $this->syncPermission('chat.supervisor', [$admin, $gerente]);
        $this->syncPermission('chat.admin', [$admin]);
        $this->syncPermission('chat.ver_estadisticas', [$admin, $gerente]);

        $this->syncPermission('gestion', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        $this->syncPermission('categorias.store', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('categorias.update', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('categorias.destroy', [$admin, $gerente]);
        $this->syncPermission('tipos_producto.store', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('tipos_producto.update', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('tipos_producto.destroy', [$admin, $gerente]);

        $this->syncPermission('productos.index', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        $this->syncPermission('productos.create', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        $this->syncPermission('productos.store', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('productos.show', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        $this->syncPermission('productos.edit', [$admin, $gerente, $trabajador, $vendedor, $bodeguero, $visitante]);
        $this->syncPermission('productos.update', [$admin, $gerente, $trabajador, $vendedor, $bodeguero]);
        $this->syncPermission('productos.destroy', [$admin, $gerente]);

        $this->syncPermission('empleado.recargas.index', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('empleado.recargas.updateEstado', [$admin, $gerente, $trabajador, $vendedor]);

        $this->syncPermission('empleado.pedidos.index', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('empleado.pedidos.update', [$admin, $gerente, $trabajador, $vendedor]);

        $this->syncPermission('roles.index', [$admin]);
        $this->syncPermission('roles.store', [$admin]);
        $this->syncPermission('roles.update', [$admin]);
        $this->syncPermission('roles.destroy', [$admin]);

        $this->syncPermission('historial.clear', [$admin]);
        $this->syncPermission('tareas.index', [$admin]);

        // Permisos para mails
        $this->syncPermission('mails.index', [$admin, $gerente]);
        $this->syncPermission('mails.store', [$admin, $gerente]);
        $this->syncPermission('mails.update', [$admin, $gerente]);
        $this->syncPermission('mails.destroy', [$admin, $gerente]);

        // Permisos Donna Hub
        $this->syncPermission('donna.dashboard', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('donna.planes', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('donna.planes.store', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('donna.planes.destroy', [$admin, $gerente]);
        $this->syncPermission('donna.suscripciones', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('donna.suscripciones.store', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('donna.solicitudes', [$admin, $gerente, $trabajador, $vendedor, $visitante]);
        $this->syncPermission('donna.solicitudes.manage', [$admin, $gerente, $trabajador, $vendedor]);
        $this->syncPermission('donna.referidos', [$admin, $gerente]);
        $this->syncPermission('donna.referidos.store', [$admin, $gerente]);
        $this->syncPermission('donna.referidos.destroy', [$admin, $gerente]);

        // Permisos para vista de cuentas por servicios
        $this->syncPermission('todas_las_cuentas', [$admin, $gerente]);
        $this->syncPermission('netflix', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('disney', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('max', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('spotify', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('otras', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
        $this->syncPermission('prime', [$admin, $gerente, $trabajador, $vendedor, $tecnico]);
    }
}
