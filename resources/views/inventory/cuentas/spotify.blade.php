@extends('layouts.table')
@section('title', 'Spotify')
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ESTILOS PERSONALIZADOS PARA MODALES */
        .modal-body {
            background-color: #ffffff;
            color: #212529;
        }
        .modal-body .form-label {
            color: #495057;
            font-weight: 600;
        }
        .modal-body .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
    </style>
@endsection
@section('h1', 'Cuentas de Spotify')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Cuentas de Spotify
@endsection
@section('descripcion')
    @inject('entregaMensajeService', 'App\\Services\\EntregaMensajeService')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <p>Revisa las cuentas de Spotify activas del <strong>Negocio</strong>. Aquí podrás gestionar las cuentas de usuario
        asociadas a los servicios de streaming pertenecientes a Streamify HQ.</p>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
@endsection
@section('tablename', 'Cuentas de Spotify')
@section('table1')
    <!-- Controles de búsqueda y registros -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
            <label for="spotify-table-search" class="form-label fw-semibold">
                <i class="fas fa-search text-primary"></i> Buscar:
            </label>
            <input id="spotify-table-search"
                   type="text"
                   placeholder="Buscar cuenta..."
                   class="form-control">
        </div>
        <div class="col-lg-4 col-md-5 col-12">
            <label for="spotify-table-rows-per-page" class="form-label fw-semibold">
                <i class="fas fa-list text-primary"></i> Mostrar:
            </label>
            <select id="spotify-table-rows-per-page" class="form-select">
                <option value="5">5 registros</option>
                <option value="10" selected>10 registros</option>
                <option value="20">20 registros</option>
                <option value="50">50 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="spotify-table" data-table="spotify-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th class="sortable" data-type="number" data-col="0">
                    ID
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="1">
                    Perfil
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="2">
                    Datos
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="3">
                    Vence Plan
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="4">
                    Usuario
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($cuentas as $cuenta)
                @php
                    // Convertir la fecha de vencimiento a Carbon
                    $fechaVencimiento = \Carbon\Carbon::parse($cuenta->fechavencue);
                    $hoy = \Carbon\Carbon::today();
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                @endphp
                @foreach ($cuenta->perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->idcue }}</td>
                        <td>
                            @if ($perfil->numeroper == 1)
                                <span class="badge bg-success">Admin</span>
                            @else
                                {{ $perfil->numeroper }}
                            @endif
                        </td>
                        <td>
                            @if ($perfil->numeroper == 1)
                                {{ $cuenta->usuariocue }} {{ $cuenta->contrasenacue }}
                            @else
                                {{ $perfil->pinper }}
                            @endif
                        </td>
                        <td>{{ $cuenta->fechavencue }}</td>
                        <td>
                            @if ($perfil->num_usuarios == 0)
                                Libre
                            @else
                                @foreach ($perfil->usuarios as $usuario)
                                    @php
                                        $fechaVencimientoPerfil = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                                        $hoy = \Carbon\Carbon::today();
                                        $diasRestantes = $hoy->diffInDays($fechaVencimientoPerfil, false);
                                    @endphp
                                    @if ($diasRestantes <= 0)
                                        <span class="badge bg-danger">{{ $usuario->nombre_cliente }} (Vencido)</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-exchange"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
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
                                        <br>
                                    @elseif ($diasRestantes <= 3)
                                        <span class="badge bg-warning">{{ $usuario->nombre_cliente }}
                                            {{ $usuario->fecha_vencimiento }}</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-exchange"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                            <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        @endif
                                        <br>
                                    @else
                                        <span class="badge bg-success">{{ $usuario->nombre_cliente }}
                                            {{ $usuario->fecha_vencimiento }}</span>
                                        @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                            <form action="{{ route('usuarios.moverUsuario', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-dark btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario?')">
                                                    <i class="fas fa-random"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('usuarios.moverUsuarioMesa', $usuario->iddet) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-circle btn-sm"
                                                    onclick="return confirm('Mudar este usuario a la mesa de trabajo?')">
                                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <br>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                            <td>
                                @if (Auth::user()->hasPermissionTo('perfil.update'))
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm btn-edit-profile"
                                        data-idper="{{ $perfil->idper }}"
                                        data-numeroper="{{ $perfil->numeroper }}"
                                        data-pinper="{{ $perfil->pinper }}"
                                    >
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </button>
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                    @php
                                        $mensajeEntregaPerfil = $entregaMensajeService->mensajeEntregaDesdePerfil($perfil);
                                    @endphp
                                    <button
                                        class="btn btn-success btn-sm"
                                        onclick="copyMessage({{ json_encode($mensajeEntregaPerfil) }})"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    </div>

    <!-- Información de paginación y controles -->
    <div class="row mt-3 align-items-center">
        <div class="col-md-6 col-12 mb-2 mb-md-0">
            <div id="spotify-table-row-info" class="text-muted"></div>
        </div>
        <div class="col-md-6 col-12">
            <div id="spotify-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
        </div>
    </div>
@endsection

@section('modals')
    @include('inventory.cuentas.modals.edit-profile')
@endsection

@section('scripts')
    {{-- Enhanced Table v2 --}}
    <script src="{{ asset('js/enhanced-table-v2.js') }}?v={{ filemtime(public_path('js/enhanced-table-v2.js')) }}"></script>

    <script>
        // Event listeners para botones de editar perfil
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-edit-profile').forEach(function(button) {
                button.addEventListener('click', function() {
                    const idper = this.getAttribute('data-idper');
                    const numeroper = this.getAttribute('data-numeroper');
                    const pinper = this.getAttribute('data-pinper');

                    document.getElementById('edit_profile_id').value = idper;
                    document.getElementById('edit_profile_numero').value = numeroper;
                    document.getElementById('edit_profile_pin').value = pinper;

                    // Abrir modal
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-profile' }));
                });
            });
        });

        // Función para enviar el formulario de editar perfil
        function submitEditProfile() {
            const idper = document.getElementById('edit_profile_id').value;
            const pinper = document.getElementById('edit_profile_pin').value;

            if (!pinper || pinper.trim() === '') {
                alert('Por favor ingresa un PIN válido');
                return;
            }

            const url = "{{ route('perfil.update', ':idper') }}".replace(':idper', idper);

            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    pinper: pinper
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Cerrar modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-profile' }));

                    // Mostrar mensaje de éxito
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                    const tableContainer = document.querySelector('.table-responsive');
                    tableContainer.parentNode.insertBefore(alertDiv, tableContainer);

                    // Recargar después de 1 segundo
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Error al actualizar el perfil');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar el perfil. Por favor intenta nuevamente.');
            });
        }

        // Función para copiar mensaje generado en backend
        function copyMessage(message) {
            if (!message) {
                return;
            }

            var tempTextArea = document.createElement("textarea");
            tempTextArea.value = message;
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand("copy");
            document.body.removeChild(tempTextArea);

            // Mostrar mensaje de confirmación
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                El mensaje se ha copiado al portapapeles
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
@endsection
