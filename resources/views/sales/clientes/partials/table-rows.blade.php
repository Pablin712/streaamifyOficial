@foreach ($clientes as $cliente)
    <tr>
        <td>{{ $cliente->idcli }}</td>
        <td>{{ $cliente->nombrecli }}</td>
        <td>{{ $cliente->telefonocli }}</td>
        <td>{{ $cliente->email ?? 'Ninguno' }}</td>
        <td>{{ $cliente->viewClienteUsuario->usuarios ?? 0 }}</td>
        <td>${{ $cliente->viewClienteUsuario->facturado ?? 0 }}</td>
        <td>${{ $cliente->saldo }}</td>
        <td>
            @if ($cliente->email && $cliente->password)
                <span class="badge bg-success">Sí</span>
            @else
                <span class="badge bg-danger">No</span>
            @endif
        </td>
        @if (Auth::user()->hasAnyPermission(['clientes.edit', 'clientes.destroy']))
            <td>
                <div class="action-buttons">
                    @if (Auth::user()->hasPermissionTo('clientes.edit'))
                        <a href="{{ route('clientes.edit', $cliente->idcli) }}" class="btn btn-warning btn-sm" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                    @endif
                    @if ($cliente->usuarios->isEmpty())
                        @if (Auth::user()->hasPermissionTo('clientes.destroy'))
                            <form action="{{ route('clientes.destroy', $cliente->idcli) }}" method="POST"
                                style="display: inline;"
                                onsubmit="return confirm('¿Estás seguro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
