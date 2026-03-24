{{-- Contenido de perfiles para AJAX --}}
<div class="row mb-3">
    @if (Auth::user()->hasPermissionTo('usuarios.change'))
        <div class="col-12">
            <button
                type="button"
                class="btn btn-danger mb-2 btn-move-all-mesa"
                data-cuenta-id="{{ $cuenta->idcue }}"
                data-cuenta-nombre="{{ $cuenta->usuariocue }}"
            >
                <i class="fas fa-random"></i> Mover todos los clientes a Mesa de Trabajo
            </button>
            <button
                type="button"
                class="btn btn-warning mb-2 btn-move-all-disperso"
                data-cuenta-id="{{ $cuenta->idcue }}"
                data-cuenta-nombre="{{ $cuenta->usuariocue }}"
            >
                <i class="fas fa-random"></i> Mover todos los clientes a otro lugar
            </button>
        </div>
    @endif
</div>

<div class="table-responsive">
    @inject('entregaMensajeService', 'App\\Services\\EntregaMensajeService')
    <table class="table table-sm">
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
                                        <button
                                            type="button"
                                            class="btn btn-dark btn-circle btn-sm btn-move-user"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-exchange"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-info btn-circle btn-sm btn-move-user-mesa"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-arrow-right-to-bracket"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-circle btn-sm btn-move-user-otro-servicio"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                            data-servicio="{{ $cuenta->valor->idser }}"
                                            title="Mover a otro servicio"
                                        >
                                            <i class="fas fa-retweet"></i>
                                        </button>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('ventas.renew'))
                                        <a href="{{ route('ventas.renew', ['idcli' => $usuario->idcli, 'idven' => $usuario->idven]) }}"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermissionTo('usuarios.destroy'))
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-circle btn-sm btn-delete-user"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                    <br>
                                @elseif ($diasRestantes <= 3)
                                    <span class="badge bg-warning">{{ $usuario->nombre_cliente }}
                                        {{ $usuario->fecha_vencimiento }}</span>
                                    @if (Auth::user()->hasPermissionTo('usuarios.change'))
                                        <button
                                            type="button"
                                            class="btn btn-dark btn-circle btn-sm btn-move-user"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-exchange"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-info btn-circle btn-sm btn-move-user-mesa"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-arrow-right-to-bracket"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-circle btn-sm btn-move-user-otro-servicio"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                            data-servicio="{{ $cuenta->valor->idser }}"
                                            title="Mover a otro servicio"
                                        >
                                            <i class="fas fa-retweet"></i>
                                        </button>
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
                                        <button
                                            type="button"
                                            class="btn btn-dark btn-circle btn-sm btn-move-user"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-random"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-info btn-circle btn-sm btn-move-user-mesa"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                        >
                                            <i class="fas fa-arrow-right-to-bracket"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-circle btn-sm btn-move-user-otro-servicio"
                                            data-iddet="{{ $usuario->iddet }}"
                                            data-nombre="{{ $usuario->nombre_cliente }}"
                                            data-servicio="{{ $cuenta->valor->idser }}"
                                            title="Mover a otro servicio"
                                        >
                                            <i class="fas fa-retweet"></i>
                                        </button>
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
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end"><strong>Total de Usuarios activos:</strong></td>
                <td id="totalUsuariosActivos"><strong>0</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
