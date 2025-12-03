@extends('layouts.static')
@section('h1', 'Perfiles de la Cuenta')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Perfiles de {{ $cuenta->usuariocue }}
@endsection

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

@section('introduccion')
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    @if (session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <p>En esta sección puedes ver los perfiles asociados a la cuenta de suscripción
        <strong>{{$cuenta->idcue}} {{$cuenta->usuariocue }}</strong>.
        Puedes editar el PIN de cada perfil o ver los datos de acceso de la cuenta.
    </p>
    <div class="row">
        @if (Auth::user()->hasPermissionTo('usuarios.change'))
            <form action="{{ route('cuentas.moverClientes') }}" method="POST" class="mb-3"
                onsubmit="return confirm('¿Estás seguro de mover TODOS los clientes de esta cuenta a la Mesa de Trabajo? Esta acción no se puede deshacer.');">
                @csrf
                <input type="hidden" name="cuenta_origen" value="{{ $cuenta->idcue }}">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-random"></i> Mover todos los clientes a Mesa de Trabajo
                </button>
            </form>
        @endif
        @if (Auth::user()->hasPermissionTo('usuarios.change'))
            <form action="{{ route('cuentas.moverClientesDisperso') }}" method="POST" class="mb-3"
                onsubmit="return confirm('¿Estás seguro de mover TODOS los clientes de esta cuenta a otras cuentas disponibles? Esta acción no se puede deshacer.');">
                @csrf
                <input type="hidden" name="cuenta_origen" value="{{ $cuenta->idcue }}">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-random"></i> Mover todos los clientes a otro lugar
                </button>
            </form>
        @endif
    </div>
@endsection
@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>Número de Perfil</th>
                    <th>PIN del Perfil</th>
                    <th>Num Usuarios</th>
                    <th>Usuarios Activos</th>
                    @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'perfil.update']))
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->numeroper }}</td>
                        <td>{{ $perfil->pinper }}</td>
                        <td class="usuarios-activos">
                            <span
                                class="{{ $perfil->usuarios_activos == 0 ? 'badge bg-danger' : ($perfil->usuarios_activos == 1 ? 'badge bg-success' : 'badge bg-dark') }}">
                                {{ $perfil->usuarios_activos }}
                            </span>
                        </td>
                        <td>
                            @if ($perfil->usuarios_activos == 0)
                                <span class="badge-success">Libre</span>
                            @else
                                @foreach ($perfil->usuarios as $usuario)
                                    @php
                                        $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
                                        $hoy = \Carbon\Carbon::today();
                                        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
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
                                    <button
                                        class="btn btn-success btn-sm"
                                        onclick="copyMessage(
                                            {{ json_encode($perfil->cuenta->idcue) }},
                                            {{ json_encode($perfil->cuenta->usuariocue) }},
                                            {{ json_encode($perfil->cuenta->contrasenacue) }},
                                            {{ json_encode($perfil->numeroper) }},
                                            {{ json_encode($perfil->pinper) }},
                                            {{ json_encode($perfil->cuenta->valor->bot ?? '') }}
                                        )"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end"><strong>Total de Usuarios activos:</strong></td>
                    <td id="totalUsuariosActivos"><strong>0</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection

@section('modals')
    @include('inventory.cuentas.modals.edit-profile')
@endsection

@section('scripts')
    <script>
        // Total de usuarios activos
        document.addEventListener('DOMContentLoaded', function() {
            var totalUsuarios = 0;
            var usuariosActivos = document.querySelectorAll('.usuarios-activos');
            usuariosActivos.forEach(function(item) {
                totalUsuarios += parseInt(item.textContent) || 0;
            });
            document.getElementById('totalUsuariosActivos').textContent = totalUsuarios;

            // Event listeners para botones de editar perfil
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
                    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.table'));

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

        // Función para copiar mensaje
        function copyMessage(idcue, usuariocue, contrasenacue, numeroper, pinper, bot) {
            var servicio = idcue.replace(/[^a-zA-Z]/g, '');
            var message = "*" + servicio + "*\n";
            message += "Usuario: " + usuariocue + "\n";
            message += "Clave: " + contrasenacue + "\n";
            message += "PIN de perfil Nro " + numeroper + ": " + pinper + "\n";
            message += "*Prohibido:* Modificar perfiles o contraseñas.\n";

            // Verificar si el bot no está vacío
            if (bot && bot.trim() !== "") {
                message += "\n\n*Nota importante:*\n";
                message += "Te daré acceso al bot de códigos. Si en algún momento se te solicita un código de acceso (Hogar), puedes obtenerlo ingresando al siguiente enlace:\n";
                message += bot + "\n";
                message += "¡Gracias por tu confianza!";
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
