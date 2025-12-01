@extends('layouts.table')
@section('title')
    Usuarios
@endsection
@section('styles')
    <style>
        .btn-rosa-1 {
            background: #f8bbd0;
            color: #fff;
        }

        .btn-rosa-2 {
            background: #f48fb1;
            color: #fff;
        }

        .btn-rosa-3 {
            background: #f06292;
            color: #fff;
        }

        .btn-rosa-4 {
            background: #ec407a;
            color: #fff;
        }

        .btn-rosa-5 {
            background: #ad1457;
            color: #fff;
        }

        .btn-rosa-1:hover,
        .btn-rosa-2:hover,
        .btn-rosa-3:hover,
        .btn-rosa-4:hover,
        .btn-rosa-5:hover {
            filter: brightness(0.95);
        }
    </style>
@endsection
@section('h1')
    Usuarios
@endsection
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Usuarios Activos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Muestra vista de todos los usuarios y sus fecha de caducidad para controlar actividad.</p>
@endsection
@section('tablename', 'Usuarios')
@section('btncrear')
    @if (Auth::user()->hasPermissionTo('ventas.create'))
        <a href="{{ route('ventas.create') }}" class="btn btn-primary">Nueva Venta</a>
    @endif
@endsection
@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="usuarios-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="usuarios-table-search"
                   type="text"
                   placeholder="Buscar usuario..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="usuarios-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="usuarios-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <form id="form-borrar-usuarios" action="{{ route('usuarios.destroyMultiple') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="mb-2">
            Marcar todos
            <input type="checkbox" id="check-todos">
            <button type="submit" class="btn btn-danger btn-sm" id="btn-borrar-seleccionados" disabled>
                <i class="fas fa-trash"></i> Borrar seleccionados
            </button>
        </div>

        <div class="table-responsive">
            <table id="usuarios-table" data-table="usuarios-table" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th data-type="actions">
                        ✅
                    </th>
                    <th class="sortable" data-type="string" data-col="1">
                        Cliente
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="2">
                        Teléfono
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="number" data-col="3">
                        ID Cuenta
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="4">
                        Usuario Cuenta
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="5">
                        Perfil
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="6">
                        Vencimiento
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    <th class="sortable" data-type="string" data-col="7">
                        Estado
                        <span class="sort-arrow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                            </svg>
                        </span>
                    </th>
                    @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                        <th data-type="actions">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    @php
                        $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                        $hoy = \Carbon\Carbon::today();
                        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                    @endphp
                    <tr>
                        <td>
                            @if ($diasRestantes <= 3)
                                <input type="checkbox" name="usuarios[]" value="{{ $usuario->iddet }}"
                                    class="check-usuario">
                            @endif
                        </td>
                        <td>{{ $usuario->nombre_cliente }}</td>
                        <td>{{ $usuario->cliente->telefonocli }}</td>
                        <td>{{ $usuario->idcue }}</td>
                        <td>{{ $usuario->cuenta->usuariocue }}</td>
                        <td>{{ $usuario->perfil }}</td>
                        <td>{{ $usuario->fecha_vencimiento }}</td>
                        <td>
                            @if ($diasRestantes <= 0)
                                <span class="badge bg-danger">Vencida</span>
                            @elseif ($diasRestantes <= 3)
                                <span class="badge bg-warning">Ya vence</span>
                            @else
                                <span class="badge bg-success">Activo</span>
                            @endif
                        </td>
                        @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                            <td>
                                @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                    <a href="{{ route('usuarios.change', $usuario->iddet) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    <a href="#" class="btn btn-dark btn-circle btn-sm btn-mover-usuario"
                                        data-id="{{ $usuario->iddet }}"
                                        onclick="event.preventDefault(); moverUsuario({{ $usuario->iddet }});">
                                        <i class="fas fa-random"></i>
                                    </a>
                                @endif
                                @if ($diasRestantes <= 3)
                                    <button type="button" class="btn btn-rosa-3 btn-sm"
                                        onclick="copiarMensaje('{{ $usuario->nombre_cliente }}', '{{ $usuario->fecha_vencimiento }}', '{{ $usuario->cuenta->usuariocue }}')"
                                        title="Copiar mensaje">
                                        <i class="fas fa-comment-alt"></i>
                                    </button>
                                    @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                        <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('usuarios.destroy'))
                                        <form action="{{ route('usuarios.destroy', $usuario->iddet) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-circle btn-sm"
                                                onclick="return confirm('¿Eliminar este usuario?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay usuarios activos disponibles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Información de paginación y controles -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6 col-12 mb-2 mb-md-0">
                <div id="usuarios-table-row-info" class="text-muted"></div>
            </div>
            <div class="col-md-6 col-12">
                <div id="usuarios-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
            </div>
        </div>
    </form>
    <form id="form-mover-usuario" method="POST" style="display:none;">
        @csrf
    </form>
    <div id="toast-mensaje"
        style="
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 12px 16px;
    border-radius: 6px;
    z-index: 9999;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    font-weight: bold;
    ">
        ✅ Mensaje copiado
    </div>
@endsection
@section('scripts')
    @parent
    <script>
        function moverUsuario(iddet) {
            if (confirm('Mudar este usuario?')) {
                var form = document.getElementById('form-mover-usuario');
                form.action = "{{ url('admin/usuarios') }}/" + iddet + "/mover";
                form.submit();
            }
        }
        function borrarUsuario(iddet) {
            if (confirm('¿Eliminar este usuario?')) {
                var form = document.getElementById('form-borrar-individual');
                form.action = "{{ url('admin/usuarios') }}/" + iddet;
                form.submit();
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const checkTodos = document.getElementById('check-todos');
            const btnBorrar = document.getElementById('btn-borrar-seleccionados');
            const form = document.getElementById('form-borrar-usuarios');

            function getCheckUsuarios() {
                // Solo checkboxes habilitados (los visibles)
                return document.querySelectorAll('.check-usuario:not([disabled])');
            }

            function actualizarBoton() {
                const checkUsuarios = getCheckUsuarios();
                btnBorrar.disabled = !Array.from(checkUsuarios).some(chk => chk.checked);
            }

            // Seleccionar/deseleccionar todos
            if (checkTodos) {
                checkTodos.addEventListener('change', function() {
                    const checkUsuarios = getCheckUsuarios();
                    checkUsuarios.forEach(chk => {
                        chk.checked = checkTodos.checked;
                    });
                    actualizarBoton();
                });
            }

            // Si se desmarca algún checkbox individual, desmarca el "check-todos"
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('check-usuario')) {
                    actualizarBoton();
                    const checkUsuarios = getCheckUsuarios();
                    // Si todos están marcados, marca el check-todos; si no, desmárcalo
                    checkTodos.checked = Array.from(checkUsuarios).every(chk => chk.checked);
                }
            });

            // Confirmación al borrar
            form.addEventListener('submit', function(e) {
                if (!Array.from(getCheckUsuarios()).some(chk => chk.checked)) {
                    e.preventDefault();
                    alert('Debes seleccionar al menos un usuario para borrar.');
                    return;
                }
                if (!confirm('¿Seguro que deseas borrar los usuarios seleccionados?')) {
                    e.preventDefault();
                }
            });

            // Inicializar estado del botón y del check-todos al cargar
            actualizarBoton();
            // Si todos los checkboxes están marcados al cargar, marca el check-todos
            const checkUsuarios = getCheckUsuarios();
            checkTodos.checked = checkUsuarios.length > 0 && Array.from(checkUsuarios).every(chk => chk.checked);
        });
    </script>
    <script>
        function copiarMensaje(nombre, fecha, cuenta) {
            let hoy = new Date().toISOString().slice(0, 10);
            let mensaje =
                `Hola ${nombre}, su suscripción con usuario ${cuenta} se venc${fecha <= hoy ? 'ió' : 'e'} el ${fecha}. Por favor, contáctanos para renovar.`;
            navigator.clipboard.writeText(mensaje).then(() => {
                const toast = document.getElementById('toast-mensaje');
                toast.style.display = 'block';
                toast.style.opacity = 1;

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = 0;
                    setTimeout(() => toast.style.display = 'none', 500);
                }, 2000); // Mostrar por 2 segundos
            });
        }
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
