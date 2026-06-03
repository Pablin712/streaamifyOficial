@forelse ($usuarios as $usuario)
    @php
        $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
        $hoy = \Carbon\Carbon::today();
        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
        $estadoCobro = $usuario->detalle_venta->estado ?? 'PENDIENTE';
        $esCuentaDanada = (bool) (optional($usuario->cuenta)->caidacue ?? false);
        $esMesaTrabajo = str_ends_with((string) $usuario->idcue, 'Atencion');
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
                <input type="checkbox" name="usuarios[]" value="{{ $usuario->iddet }}" class="check-usuario">
            @endif
        </td>
        <td>{{ $usuario->nombre_cliente }}</td>
        <td>{{ $usuario->cliente->telefonocli }}</td>
        <td>
            <span class="fw-semibold">{{ $usuario->idcue }}</span>
            <div class="mt-1 d-flex flex-wrap gap-1 cuenta-flags">
                @if ($esCuentaDanada)
                    <span class="badge bg-danger cuenta-flag-danada">Dañada</span>
                @endif
                @if ($esMesaTrabajo)
                    <span class="badge bg-warning text-dark cuenta-flag-mesa">Mesa</span>
                @endif
            </div>
        </td>
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
                    <button
                        type="button"
                        class="btn btn-danger btn-circle btn-sm btn-move-user-otro-servicio"
                        data-iddet="{{ $usuario->iddet }}"
                        data-nombre="{{ $usuario->nombre_cliente }}"
                        data-servicio="{{ $usuario->cuenta->valor->idser ?? '' }}"
                        title="Mover a otro servicio"
                        onclick="abrirModalMoverOtroServicio(event, this)"
                    >
                        <i class="fas fa-retweet"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm btn-marcar-cuenta-danada"
                        data-iddet="{{ $usuario->iddet }}"
                        {{ $esCuentaDanada ? 'disabled' : '' }}
                        onclick="abrirModalMarcarCuentaDanada(event, {{ $usuario->iddet }}, {{ json_encode($usuario->nombre_cliente) }}, {{ json_encode($usuario->idcue) }})"
                        title="{{ $esCuentaDanada ? 'La cuenta ya está marcada como dañada' : 'Marcar cuenta como dañada' }}"
                    >
                        <i class="fas fa-triangle-exclamation"></i>
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="event.stopPropagation(); abrirModalAcomodarCuenta({{ json_encode($usuario->idcue) }}, {{ json_encode($usuario->cuenta->usuariocue) }})"
                        title="Acomodar usuarios excedentes de la cuenta"
                    >
                        <i class="fas fa-sort-amount-down"></i>
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
                <button type="button" class="btn btn-info btn-sm"
                    onclick="event.stopPropagation(); copiarTelefono('{{ $usuario->cliente->telefonocli }}')"
                    title="Copiar teléfono">
                    <i class="fas fa-phone"></i>
                </button>
                <button type="button" class="btn btn-whatsapp-green btn-sm"
                    onclick="event.stopPropagation(); enviarWhatsAppUsuario({
                        idCliente: {{ json_encode($usuario->idcli) }},
                        cliente: {{ json_encode($usuario->nombre_cliente) }},
                        telefono: {{ json_encode($usuario->cliente->telefonocli) }},
                        channelPreference: 'verde'
                    })"
                    title="Enviar WhatsApp verde">
                    <i class="fab fa-whatsapp"></i>
                </button>
                <button type="button" class="btn btn-whatsapp-blue btn-sm"
                    onclick="event.stopPropagation(); enviarWhatsAppUsuario({
                        idCliente: {{ json_encode($usuario->idcli) }},
                        cliente: {{ json_encode($usuario->nombre_cliente) }},
                        telefono: {{ json_encode($usuario->cliente->telefonocli) }},
                        channelPreference: 'azul'
                    })"
                    title="Enviar WhatsApp azul">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center">No hay usuarios activos disponibles.</td>
    </tr>
@endforelse
