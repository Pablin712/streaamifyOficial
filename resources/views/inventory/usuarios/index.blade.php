@extends('layouts.table')
@section('title')
    Usuarios
@endsection
@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />

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

        /* Toggle Switch para estado de cobro */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #28a745;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .toggle-slider.disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Fila clicable para copiar mensaje */
        tbody tr.clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        tbody tr.clickable-row:hover {
            background-color: rgba(248, 187, 208, 0.2) !important;
        }

        tbody tr.clickable-row td {
            position: relative;
        }

        /* Evitar que los clicks en botones activen la fila */
        tbody tr.clickable-row .btn,
        tbody tr.clickable-row .toggle-switch,
        tbody tr.clickable-row input[type="checkbox"] {
            pointer-events: auto;
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
                    <th class="sortable" data-type="string" data-col="8">
                        Cobro
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
                        $estadoCobro = $usuario->detalle_venta->estado ?? 'PENDIENTE';
                        $pinPerfil = optional($usuario->profile)->pinper
                            ?? optional($usuario->cuenta->perfiles->firstWhere('numeroper', $usuario->perfil))->pinper;
                    @endphp
                    <tr class="{{ $diasRestantes <= 3 ? 'clickable-row' : '' }}"
                        data-iddet="{{ $usuario->iddet }}"
                        data-servicio="{{ $usuario->cuenta->valor->servicio->nombreser ?? 'Servicio' }}"
                        data-cuenta="{{ $usuario->cuenta->usuariocue }}"
                        data-contrasena="{{ $usuario->cuenta->contrasenacue }}"
                        data-perfil="{{ $usuario->perfil }}"
                        data-pinper="{{ $pinPerfil }}"
                        data-fecha="{{ $usuario->fecha_vencimiento }}"
                        data-estado="{{ $fechaVencimiento <= $hoy ? 'Vencida' : ($diasRestantes <= 3 ? 'Ya vence' : 'Activo') }}">
                        <td onclick="event.stopPropagation();">
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
                        <td onclick="event.stopPropagation();">
                            @if ($diasRestantes <= 3)
                                <label class="toggle-switch" title="Cambiar estado de cobro">
                                    <input type="checkbox"
                                           class="toggle-estado-cobro"
                                           data-iddet="{{ $usuario->iddet }}"
                                           {{ $estadoCobro === 'COBRADO' ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <small class="d-block mt-1 text-{{ $estadoCobro === 'COBRADO' ? 'success' : 'warning' }}">
                                    {{ $estadoCobro }}
                                </small>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                        @if (Auth::user()->hasAnyPermission(['usuarios.change', 'ventas.renew', 'usuarios.destroy']))
                            <td>
                                @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                    <button type="button" class="btn btn-warning btn-sm btn-cambiar-usuario"
                                        data-iddet="{{ $usuario->iddet }}"
                                        data-nombrecli="{{ $usuario->nombre_cliente }}"
                                        data-idven="{{ $usuario->idven }}"
                                        data-idcue="{{ $usuario->idcue }}"
                                        data-perfil="{{ $usuario->perfil }}"
                                        data-fecha="{{ $usuario->fecha_vencimiento }}"
                                        title="Cambiar usuario">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-dark btn-circle btn-sm"
                                        onclick="abrirModalMoverUsuario({{ $usuario->iddet }})"
                                        title="Mover usuario">
                                        <i class="fas fa-random"></i>
                                    </button>
                                @endif
                                @if ($diasRestantes <= 3)
                                    <button type="button" class="btn btn-rosa-3 btn-sm"
                                        onclick="copiarMensaje(
                                            {{ json_encode($usuario->cuenta->valor->servicio->nombreser ?? 'Servicio') }},
                                            {{ json_encode($usuario->cuenta->usuariocue) }},
                                            {{ json_encode($usuario->cuenta->contrasenacue) }},
                                            {{ json_encode($usuario->perfil) }},
                                            {{ json_encode($usuario->fecha_vencimiento) }},
                                            {{ json_encode($diasRestantes <= 0 ? 'Vencida' : ($diasRestantes <= 3 ? 'Ya vence' : 'Activo')) }},
                                            {{ json_encode($pinPerfil) }}
                                        )"
                                        title="Copiar mensaje">
                                        <i class="fas fa-comment-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm"
                                        onclick="event.stopPropagation(); copiarTelefono('{{ $usuario->cliente->telefonocli }}')"
                                        title="Copiar teléfono">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                    @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                        <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('usuarios.destroy'))
                                        <button type="button" class="btn btn-danger btn-circle btn-sm"
                                            onclick="confirmarEliminarUsuario({{ $usuario->iddet }})"
                                            title="Eliminar usuario">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No hay usuarios activos disponibles.</td>
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

<!-- Modales -->
@include('inventory.usuarios.modals.change')
@include('inventory.usuarios.modals.delete')
@include('inventory.usuarios.modals.mover')
@endsection
@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>

    @parent
    <script>
        // ========================================================================
        // FUNCIÓN DE MODAL - CAMBIAR USUARIO
        // ========================================================================
        function cambiarUsuario(iddet, nombrecli, idven, idcue, perfil, fechaVencimiento) {
            console.log('🔷 Abriendo modal de cambiar usuario:', iddet);

            document.getElementById('changeUsuarioModalTitle').textContent = 'Actualizar Usuario - ' + nombrecli;
            document.getElementById('change_nombrecli').value = nombrecli;
            document.getElementById('change_idven').value = idven;
            document.getElementById('change_perfil').value = perfil;
            document.getElementById('change_fecha_vencimiento').value = fechaVencimiento;

            const form = document.getElementById('changeUsuarioForm');
            form.action = "{{ route('usuarios.update', '') }}/" + iddet;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'cambiar-usuario' }));

            // Inicializar Select2 después de abrir el modal
            setTimeout(function() {
                const $select = $('#change_idcue');

                // Destruir instancia previa si existe
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                // Inicializar Select2
                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Selecciona una Cuenta --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('.modal-overlay:visible .modal-content'),
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        }
                    }
                });

                // Setear el valor seleccionado
                $select.val(idcue).trigger('change');

                // Asegurar que el dropdown se posicione correctamente
                $select.on('select2:open', function() {
                    $('.select2-dropdown').css('z-index', 99999);
                });
            }, 400);
        }

        // Event listener para botones de cambiar usuario
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-cambiar-usuario')) {
                    const btn = e.target.closest('.btn-cambiar-usuario');
                    const iddet = btn.dataset.iddet;
                    const nombrecli = btn.dataset.nombrecli;
                    const idven = btn.dataset.idven;
                    const idcue = btn.dataset.idcue;
                    const perfil = btn.dataset.perfil;
                    const fecha = btn.dataset.fecha;
                    cambiarUsuario(iddet, nombrecli, idven, idcue, perfil, fecha);
                }
            });
        });

        // ========================================================================
        // FUNCIÓN DE MODAL - MOVER USUARIO
        // ========================================================================
        function abrirModalMoverUsuario(iddet) {
            console.log('🔷 Abriendo modal de mover usuario:', iddet);

            const form = document.getElementById('moverUsuarioForm');
            form.action = "{{ url('admin/usuarios') }}/" + iddet + "/mover";

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mover-usuario' }));
        }

        // ========================================================================
        // FUNCIÓN DE MODAL - ELIMINAR USUARIO
        // ========================================================================
        function confirmarEliminarUsuario(iddet) {
            console.log('🔷 Abriendo modal de eliminar usuario:', iddet);

            const form = document.getElementById('deleteUsuarioForm');
            form.action = "{{ route('usuarios.destroy', '') }}/" + iddet;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirmar-eliminar-usuario' }));
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
        function copiarMensaje(servicio, cuenta, contrasenaCuenta, perfilNumero, fecha, estado, pinper = '') {
            const servicioTexto = (servicio || '').toString().trim();
            const esSpotify = servicioTexto.toUpperCase().includes('SPOTIFY');
            let mensaje = `Hola, tu cuenta de *${servicio}* - *${cuenta}* vence el *${fecha}* y su estado es *${estado}*`;

            if (esSpotify) {
                if (Number(perfilNumero) === 1) {
                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n👤 Usuario: *${cuenta}*\n🔑 Clave: *${contrasenaCuenta}*`;
                } else {
                const pinTexto = (pinper || '').toString().trim();

                if (pinTexto) {
                    let usuarioSpotify = pinTexto;
                    let claveSpotify = pinTexto;

                    if (pinTexto.includes('|')) {
                        const credenciales = pinTexto.split('|');
                        usuarioSpotify = (credenciales[0] || '').trim();
                        claveSpotify = (credenciales[1] || '').trim();
                    }

                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n👤 Usuario: *${usuarioSpotify}*\n🔑 Clave: *${claveSpotify}*`;
                } else {
                    mensaje = `Hola, tu cuenta de *${servicio}* vence el *${fecha}* y su estado es *${estado}*\n⚠️ No hay pinper configurado para este perfil.`;
                }
                }
            }

            navigator.clipboard.writeText(mensaje).then(() => {
                const toast = document.getElementById('toast-mensaje');
                toast.textContent = '✅ Mensaje copiado';
                toast.style.display = 'block';
                toast.style.opacity = 1;

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = 0;
                    setTimeout(() => toast.style.display = 'none', 500);
                }, 2000);
            });
        }

        function copiarTelefono(telefono) {
            navigator.clipboard.writeText(telefono).then(() => {
                const toast = document.getElementById('toast-mensaje');
                toast.textContent = '✅ Teléfono copiado';
                toast.style.display = 'block';
                toast.style.opacity = 1;

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = 0;
                    setTimeout(() => toast.style.display = 'none', 500);
                }, 2000);
            });
        }

        // ========================================================================
        // FUNCIONALIDAD: CLICK EN FILA PARA COPIAR MENSAJE
        // ========================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar click en filas para copiar mensaje
            document.addEventListener('click', function(e) {
                const row = e.target.closest('tr.clickable-row');
                if (row) {
                    const servicio = row.dataset.servicio;
                    const cuenta = row.dataset.cuenta;
                    const contrasenaCuenta = row.dataset.contrasena || '';
                    const perfilNumero = row.dataset.perfil || '';
                    const pinper = row.dataset.pinper || '';
                    const fecha = row.dataset.fecha;
                    const estado = row.dataset.estado;
                    copiarMensaje(servicio, cuenta, contrasenaCuenta, perfilNumero, fecha, estado, pinper);
                }
            });

            // ========================================================================
            // FUNCIONALIDAD: TOGGLE ESTADO DE COBRO CON AJAX
            // ========================================================================
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('toggle-estado-cobro')) {
                    const toggle = e.target;
                    const iddet = toggle.dataset.iddet;
                    const nuevoEstado = toggle.checked ? 'COBRADO' : 'PENDIENTE';
                    const slider = toggle.nextElementSibling;

                    // Buscar el elemento small que muestra el estado (puede estar dentro o fuera del label)
                    const toggleLabel = toggle.closest('label');
                    const labelEstado = toggleLabel.parentElement.querySelector('small');

                    // Deshabilitar el toggle mientras se procesa
                    toggle.disabled = true;
                    slider.classList.add('disabled');

                    // Construir la URL con el ID dinámico
                    const url = "{{ route('usuarios.actualizarEstadoCobro', ['id' => '__ID__']) }}".replace('__ID__', iddet);

                    // Hacer petición AJAX
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            estado: nuevoEstado
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar el label del estado si existe
                            if (labelEstado) {
                                labelEstado.textContent = nuevoEstado;
                                labelEstado.className = `d-block mt-1 text-${nuevoEstado === 'COBRADO' ? 'success' : 'warning'}`;
                            }

                            // Mostrar toast de éxito
                            const toast = document.getElementById('toast-mensaje');
                            toast.textContent = `✅ Estado actualizado a ${nuevoEstado}`;
                            toast.style.display = 'block';
                            toast.style.opacity = 1;

                            setTimeout(() => {
                                toast.style.transition = 'opacity 0.5s ease';
                                toast.style.opacity = 0;
                                setTimeout(() => toast.style.display = 'none', 500);
                            }, 2000);
                        } else {
                            // Revertir el toggle si hay error
                            toggle.checked = !toggle.checked;
                            alert('Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revertir el toggle si hay error
                        toggle.checked = !toggle.checked;
                        alert('Error al actualizar el estado. Por favor, intenta de nuevo.');
                    })
                    .finally(() => {
                        // Re-habilitar el toggle
                        toggle.disabled = false;
                        slider.classList.remove('disabled');
                    });
                }
            });
        });
    </script>

    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}"></script>
@endsection
