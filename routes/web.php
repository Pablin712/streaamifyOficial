<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ValorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContabilidadController;
use App\Http\Controllers\CostoController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TipoGastoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\EmailController;
use App\Http\Middleware\AuthCliente;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CodigoController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\LoginClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\BancoController;

// === RUTAS DE DEMO CHAT ===
Route::get('/chat/demo-widget', function () {
    return view('chat.demo-widget');
})->name('chat.demo.widget');

Route::get('/chat/panel', function () {
    return view('chat.panel-empleados');
})->name('chat.panel')->middleware('auth');
// === FIN RUTAS DEMO ===
use App\Http\Controllers\RecargaController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HistorialClientesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MailController;
use App\Http\Controllers\AlyssonController;
use App\Http\Controllers\SistemaController;
//cliente
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/', function () {
    return view('principal');
})->name('principal');

Route::controller(AlyssonController::class)->group(function (){
    Route::get('/alysson', 'exclusive')->name('alysson.exclusive');
});
Route::get('/tutorial', function () {
    return view('shopping.tutorial');
})->name('tutorial');

Route::controller(ShopController::class)->group(function () {
    Route::get('/shop', 'index')->name('shop');
    Route::post('/cart/add/{id}', 'addToCart')->name('cart.add');
    Route::get('/cart', 'viewCart')->name('cart.view');
    Route::post('/cart/remove/{id}', 'removeFromCart')->name('cart.remove');
    Route::post('/cart/clear', 'clearCart')->name('cart.clear');
    Route::get('/checkout', 'checkout')->name('cart.checkout');
});

Route::controller(LoginClienteController::class)->group(function () {
    Route::get('/cliente/login', 'showLoginForm')->name('cliente.login');
    Route::post('/cliente/login', 'login')->name('cliente.login.submit');
    Route::post('/cliente/logout', 'logout')->name('cliente.logout');
    Route::get('/cliente/recover', function () {
        return view('auth.recoverCliente');
    })->name('cliente.recover');
});
Route::post('/register', [ClienteController::class, 'register'])->name('cliente.register');

//rutas protegidas del cliente
Route::prefix('/cliente')->middleware([AuthCliente::class])->group(function () {
    Route::controller(RecargaController::class)->group(function () {
        Route::get('/recargar-saldo', 'recargarSaldo')->name('recargar.index');
        Route::post('/recargar-saldo', 'procesarRecarga')->name('recargar.saldo');
    });
    Route::controller(HistorialClientesController::class)->group(function () {
        Route::get('/historial-cliente', 'index')->name('historial.cliente');
        Route::post('/renovar/{id}', 'renovar')->name('cliente.renovar');
    });
    Route::controller(ShopController::class)->group(function () {
        Route::post('/comprar/{id}', 'comprar')->name('comprar');
        Route::post('/renovar/{id}', 'renovar')->name('cliente.renovar');
    });
    Route::controller(ClienteController::class)->group(function () {
        Route::get('/perfil', 'perfil')->name('cliente.perfil');
        Route::put('/perfil/update', 'actualizarPerfil')->name('cliente.perfil.update');
        Route::put('/cambiar-contrasena', 'cambiarContrasena')->name('cliente.cambiar.contrasena');
    });
    Route::controller(CodigoController::class)->group(function () {
        Route::get('/codigo', 'index')->name('codigo.index');
    });
});
/*
Route::post('/cart/add/{producto}', [CarritoController::class, 'add'])->name('cart.add');
Route::post('/comprar/{producto}', [CompraController::class, 'comprar'])->name('comprar');
Route::post('/pedido/{producto}', [CompraController::class, 'pedido'])->name('pedido');
*/

// administración
Route::get('/admin', HomeController::class);

Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('login'); // Muestra la vista del login
Route::post('/admin/login', [LoginController::class, 'login'])->name('login.submit');                      // Procesa el formulario del login
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('logout');    // Cierra la sesión
Route::get('/admin/recover', function () {
    return view('auth.recover');
})->name('recover');

Route::post('/cliente/recover', [EmailController::class, 'sendRecoverClienteEmail'])->name('recoverCliente.email');
Route::post('/admin/recover', [EmailController::class, 'sendRecoverEmail'])->name('recover.email');

Route::prefix('/admin')->middleware(['auth'])->group(function () {
    Route::post('/asistencias/ping', [AsistenciaController::class, 'ping'])->name('asistencias.ping');
    Route::get('/empleados/asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
    //rutas de navegación en negocio
    Route::get('/inicio', [InicioController::class, 'show'])->name('inicio');
    Route::get('/historial', [HistorialController::class, 'show'])->name('historial');
    Route::post('/historial/clear', [HistorialController::class, 'clear'])->name('historial.clear');

    Route::resource('tareas', TareaController::class);
    Route::patch('/tareas/{id}/completar', [TareaController::class, 'completar'])->name('tareas.completar');
    //Route::middleware(['auth:administrador,contador'])->group(function () {
    Route::controller(ContabilidadController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::post('/dashboard/createstore', 'store')->name('dashboard.store');
        Route::get('/dashboard/filter', 'filterData')->name('dashboard.filter');
        Route::get('/dashboard/pdf', 'generarPDF')->name('dashboard.pdf');
    });
    Route::controller(CalendarController::class)->group(function () {
        Route::get('/calendario', 'index')->name('calendario');
    });

    Route::controller(BancoController::class)->group(function () {
        Route::get('/bancos', 'index')->name('bancos.index')->middleware('permission:bancos.index');
        Route::post('/bancos', 'store')->name('bancos.store')->middleware('permission:bancos.store');
        Route::put('/bancos/{id}', 'update')->name('bancos.update')->middleware('permission:bancos.update');
        Route::post('/bancos/{banco_id}/transacciones', 'registrarTransaccion')->name('bancos.transacciones.store')->middleware('permission:bancos.transacciones.store');
        Route::post('/bancos/transferir', 'transferirFondos')->name('bancos.transferir')->middleware('permission:bancos.transacciones.store');
        Route::put('/bancos/deudas/{id}/pagar', 'pagarDeuda')->name('bancos.pagar-deuda')->middleware('permission:bancos.transacciones.store');
    });

    Route::controller(CostoController::class)->group(function () {
        Route::get('/costos', 'index')->name('costos');
        Route::post('/costos', 'store')->name('costos.store');
        Route::put('/costos/{id}', 'update')->name('costos.update');
        Route::delete('/costos/{id}', 'destroy')->name('costos.destroy');
    });

    Route::controller(GastoController::class)->group(function () {
        Route::get('/gastos', 'index')->name('gastos');
        Route::post('/gastos', 'store')->name('gastos.store');
        Route::put('/gastos/{id}', 'update')->name('gastos.update');
        Route::delete('/gastos/{id}', 'destroy')->name('gastos.destroy');
    });

    Route::controller(TipoGastoController::class)->group(function () {
        Route::get('/tipos', 'index')->name('tipos');
        Route::post('/tipos', 'store')->name('tipos.store');
        Route::put('/tipos/{id}', 'update')->name('tipos.update');
        Route::delete('/tipos/{id}', 'destroy')->name('tipos.destroy');
    });
    //Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
    Route::controller(ServicioController::class)->group(function () {
        Route::get('/servicios', 'index')->name('servicios');
        Route::get('/servicios/create', 'create')->name('servicios.create');
        Route::post('/servicios/createstore', 'store')->name('servicios.store');
        Route::get('/servicios/{idser}/edit', 'edit')->name('servicios.edit');
        Route::put('/servicios/{idser}', 'update')->name('servicios.update');
        Route::delete('/servicios/{idser}', 'destroy')->name('servicios.destroy');
    });

    Route::controller(ValorController::class)->group(function () {
        Route::get('/valores', 'index')->name('valores');
        Route::get('/valores/create', 'create')->name('valores.create');
        Route::get('/valores/pdf', 'pdf')->name('valores.pdf');
        Route::post('/valores/createstore', 'store')->name('valores.store');
        Route::post('/valores/corregir-valores', 'corregir')->name('valores.corregir');
        Route::post('/valores/deletegroup', 'deletegroup')->name('valores.deletegroup');
        Route::get('/valores/{id}/edit', 'edit')->name('valores.edit');
        Route::put('/valores/{id}', 'update')->name('valores.update');
        Route::post('/valores/updatePantallas', 'updatePantallas')->name('valores.updatePantallas');
        Route::delete('/valores/{id}', 'destroy')->name('valores.destroy');
    });

    Route::controller(ProveedorController::class)->group(function () {
        Route::get('/proveedores', 'index')->name('proveedores');
        Route::get('/proveedores/create', 'create')->name('proveedores.create');
        Route::post('/proveedores/createstore', 'store')->name('proveedores.store');
        Route::get('/proveedores/{id}/edit', 'edit')->name('proveedores.edit');
        Route::put('/proveedores/{id}', 'update')->name('proveedores.update');
        Route::delete('/proveedores/{id}', 'destroy')->name('proveedores.destroy');
    });

    Route::controller(ClienteController::class)->group(function () {
        Route::get('/clientes', 'index')->name('clientes');
        Route::get('/clientes/create', 'create')->name('clientes.create');
        Route::post('/clientes/createstore', 'store')->name('clientes.store');
        Route::post('/clientes/storeInVenta', 'storeInVenta')->name('clientes.storeInVenta');
        Route::get('/clientes/{id}/edit', 'edit')->name('clientes.edit');
        Route::put('/clientes/{id}', 'update')->name('clientes.update');
        Route::get('/clientes/export', 'export')->name('clientes.export');
        Route::delete('/clientes/{id}', 'destroy')->name('clientes.destroy');
    });

    Route::controller(CuentaController::class)->group(function () {
        Route::get('/cuentas', 'index')->name('cuentas');
        Route::get('/cuentas/create', 'create')->name('cuentas.create');
        Route::get('/cuentas/pdf', 'pdf')->name('cuentas.pdf');
        Route::post('/cuentas/createstore', 'store')->name('cuentas.store');
        Route::patch('/cuentas/{id}/status', 'status')->name('cuentas.status');
        Route::post('/cuentas/mover-clientes', 'moverClientes')->name('cuentas.moverClientes');
        Route::post('/cuentas/dispersar-clientes', 'moverClientesDisperso')->name('cuentas.moverClientesDisperso');
        Route::post('/cuentas/{id}/mensaje-clientes', 'enviarMensajeClientes')->name('cuentas.enviarMensajeClientes');
        Route::get('/cuentas/PerfilesSpotify', 'PerfilesSpotify')->name('cuentas.spotify');
        Route::get('/cuentas/{id}/load-perfiles', 'loadPerfiles')->name('cuentas.loadPerfiles');
        Route::get('/cuentas/{id}/edit', 'edit')->name('cuentas.edit');
        Route::get('/cuentas/{id}/show', 'show')->name('cuentas.show');
        Route::get('/cuentas/{id}/renew', 'renew')->name('cuentas.renew');
        Route::get('/cuentas/{id}', 'mensaje')->name('cuentas.mensaje');
        Route::post('/cuentas/{id}/renew', 'saveRenew')->name('cuentas.saveRenew');
        Route::put('/cuentas/{id}', 'update')->name('cuentas.update');
        Route::delete('/cuentas/{id}', 'destroy')->name('cuentas.destroy');
    });
    //Route::resource('perfil',PerfilController::class);
    Route::controller(PerfilController::class)->group(function () {
        Route::get('/perfil', 'index')->name('perfil');
        Route::put('/perfil/{id}', 'update')->name('perfil.update');
        Route::post('/perfil/createstore', 'store')->name('perfil.store');
        Route::get('/perfil/{id}/edit', 'edit')->name('perfil.edit');
    });

    Route::controller(VentaController::class)->group(function () {
        Route::get('/ventas', 'index')->name('ventas');
        Route::get('/ventas/create', 'create')->name('ventas.create');
        Route::post('/ventas/createstore', 'store')->name('ventas.store');
        Route::post('/ventas/storeRenew', 'storeRenew')->name('ventas.storeRenew');
        Route::post('/ventas/storeCliente', 'storeCliente')->name('ventas.storeCliente');
        Route::patch('/ventas/{id}/status', 'status')->name('ventas.status');
        Route::get('/ventas/{id}/edit', 'edit')->name('ventas.edit');
        Route::get('/ventas/renew/{idcli}/{idven}', 'renew')->name('ventas.renew');
        Route::put('/ventas/{id}', 'update')->name('ventas.update');
        Route::delete('/ventas/{id}', 'destroy')->name('ventas.destroy');
        Route::get('/ventas/{id}/details', 'getDetails')->name('ventas.details');
        Route::post('/ventas/{id}/sendInvoice', 'sendInvoice')->name('ventas.sendInvoice');
    });

    Route::controller(UsuarioController::class)->group(function () {
        Route::get('/usuarios', 'index')->name('usuarios');
        Route::get('/usuarios/{id}/change', 'change')->name('usuarios.change');
        Route::get('/usuarios/{id}/renew', 'renew')->name('usuarios.renew');
        Route::put('/usuarios/{id}', 'update')->name('usuarios.update');
        Route::post('/usuarios/{id}/estado-cobro', 'actualizarEstadoCobro')->name('usuarios.actualizarEstadoCobro');
        Route::post('/usuarios/{id}/mover', 'moverUsuario')->name('usuarios.moverUsuario');
        Route::post('/usuarios/{id}/mover-mesa', 'moverUsuarioMesa')->name('usuarios.moverUsuarioMesa');
        Route::post('/usuarios/{id}/mover-otro-servicio', 'moverUsuarioOtroServicio')->name('usuarios.moverUsuarioOtroServicio');
        Route::post('/usuarios/{id}/marcar-cuenta-danada', 'marcarCuentaDanada')->name('usuarios.marcarCuentaDanada');
        Route::delete('/usuariosSeleccionados/delete-multiple', 'destroyMultiple')->name('usuarios.destroyMultiple');
        Route::delete('/usuarios/{id}', 'destroy')->name('usuarios.destroy');
    });

    Route::controller(EmpleadoController::class)->group(function () {
        Route::get('/empleados', 'index')->name('empleados');
        Route::get('/empleados/create', 'create')->name('empleados.create');
        Route::post('/empleados/createstore', 'store')->name('empleados.store');
        Route::get('/empleados/{id}/edit', 'edit')->name('empleados.edit');
        Route::get('/empleados/{id}/roles', 'editRoles')->name('empleados.editRoles');
        Route::put('/empleados/{id}', 'update')->name('empleados.update');
        Route::put('/empleados/{id}/update-password', 'updatePassword')->name('empleados.updatePassword');
        Route::put('/empleados/{id}/update-roles', 'updateRoles')->name('empleados.updateRoles');
        Route::delete('/empleados/{id}', 'destroy')->name('empleados.destroy');
    });

    Route::controller(MantenimientoController::class)->group(function () {
        Route::get('/mantenimientos', 'index')->name('mantenimientos');
        Route::get('/mantenimientos/create', 'create')->name('mantenimientos.create');
        Route::post('/mantenimientos/createstore', 'store')->name('mantenimientos.store');
        Route::get('/mantenimientos/{id}/edit', 'edit')->name('mantenimientos.edit');
        Route::put('/mantenimientos/{id}', 'update')->name('mantenimientos.update');
        Route::delete('/mantenimientos/{id}', 'destroy')->name('mantenimientos.destroy');
    });

    Route::prefix('gestion-productos')->group(function () {
        // Rutas para Categorías
        Route::get('/', [CategoriaController::class, 'index'])->name('gestion.index');
        Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put('categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

        Route::post('tipos-producto', [TipoProductoController::class, 'store'])->name('tipos_producto.store');
        Route::put('tipos-producto/{id}', [TipoProductoController::class, 'update'])->name('tipos_producto.update');
        Route::delete('tipos-producto/{id}', [TipoProductoController::class, 'destroy'])->name('tipos_producto.destroy');
    });
    Route::resource('productos', ProductoController::class);
    Route::get('catalogo/pdf', [ProductoController::class, 'generarPDF'])->name('productos.pdf');
    Route::resource('roles', RoleController::class);
    Route::post('/productos/updatePrecios', [ProductoController::class, 'updatePrecios'])->name('productos.updatePrecios');

    Route::controller(RecargaController::class)->group(function () {
        Route::get('/recargas', 'index')->name('empleado.recargas.index');
        Route::post('/recargas/{id}/estado', 'updateEstado')->name('empleado.recargas.updateEstado');
    });
    Route::controller(PedidoController::class)->group(function () {
        Route::get('/empleado/pedidos', 'index')->name('empleado.pedidos.index');
        Route::post('/empleado/pedidos/{id}', 'update')->name('empleado.pedidos.update');
    });
    Route::resource('mails', MailController::class)->except(['show', 'create', 'edit']);

    // Ruta para configuración del sistema (solo administradores)
    Route::get('/sistema', [SistemaController::class, 'index'])->name('sistema.index');

    Route::post('/notificaciones/marcar-como-leidas', function () {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('notificaciones.leer');
});
