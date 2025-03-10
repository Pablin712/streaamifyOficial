<table class="datatable table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Servicio</th>
            <th>Usuario</th>
            <th>Clave</th>
            <th>Vence</th>
            <th>Clientes</th>
            <th>Estado</th>
            @if (Auth::user()->hasAnyPermission(['cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                <th>Acciones</th>
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
            <tr>
                <td>{{ $cuenta->idcue }}</td>
                <td>{{ $cuenta->valor->idser }}-{{ $cuenta->valor->proveedor->nombrepro }}</td>
                <td>{{ $cuenta->usuariocue }}</td>
                <td>{{ $cuenta->contrasenacue }}</td>
                <td>{{ $cuenta->fechavencue }}</td>
                <td>
                    @php
                        $users = $cuenta->usuarios_activos;
                    @endphp
                    @if ($cuenta->valor->pantmaxval < $users)
                        <span class="badge bg-dark">{{ $users }}</span>
                    @elseif ($cuenta->valor->pantminval > $users)
                        <span class="badge bg-danger">{{ $users }}</span>
                    @else
                        <span class="badge bg-success">{{ $users }}</span>
                    @endif
                </td>
                <td>
                    @if ($cuenta->caidacue)
                        <span class="badge bg-dark">Dañada</span>
                    @elseif ($diasRestantes <= 0)
                        <span class="badge bg-danger">Vencida</span>
                    @elseif ($diasRestantes <= 5)
                        <span class="badge bg-warning">Ya vence</span>
                    @else
                        <span class="badge bg-success">Activa</span>
                    @endif
                    @if (Auth::user()->hasPermissionTo('cuentas.status'))
                        <!-- Botón para cambiar estado -->
                        <form action="{{ route('cuentas.status', $cuenta->idcue) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-dark btn-sm">
                                @if ($cuenta->caidacue)
                                    <i class="fas fa-toggle-on fa-xs"></i>
                                @else
                                    <i class="fas fa-toggle-off fa-xs"></i>
                                @endif
                            </button>
                        </form>
                    @endif
                </td>
                @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                    <td>
                        @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                            <a href="{{ route('cuentas.show', $cuenta->idcue) }}" class="btn btn-info btn-xs">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endif
                        @if (Auth::user()->hasPermissionTo('cuentas.edit'))
                            <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                        @if ($diasRestantes <= 5 || $diasRestantes < 0)
                            @if (Auth::user()->hasPermissionTo('cuentas.renew'))
                                <a href="{{ route('cuentas.renew', $cuenta->idcue) }}"
                                    class="btn btn-success btn-xs">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            @endif
                        @endif
                        @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
                            <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('¿Estás seguro?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>