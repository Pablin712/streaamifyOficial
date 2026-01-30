@foreach ($costos as $costo)
    <tr>
        <td>{{ $costo->idcos }}</td>
        <td>
            @if($costo->cuenta)
                {{ $costo->cuenta->idcue }} - {{ $costo->cuenta->usuariocue }}
            @else
                <span class="text-muted">Sin cuenta</span>
            @endif
        </td>
        <td>{{ $costo->fechacos }}</td>
        <td>{{ $costo->descripcioncos }}</td>
        <td>${{ number_format($costo->montocos, 2) }}</td>
        @if (Auth::user()->hasAnyPermission(['costos.update', 'costos.destroy']))
            <td>
                <div class="action-buttons">
                    @if (Auth::user()->hasPermissionTo('costos.update'))
                        <button type="button"
                            class="btn btn-warning btn-sm"
                            onclick="editarCosto({{ $costo->idcos }}, '{{ $costo->idcue }}', '{{ $costo->descripcioncos }}', {{ $costo->montocos }}, '{{ $costo->fechacos }}', {{ $costo->transaccion ? $costo->transaccion->banco_id : 'null' }})"
                            title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif
                    @if (Auth::user()->hasPermissionTo('costos.destroy'))
                        <button type="button" class="btn btn-danger btn-sm"
                            onclick="confirmarEliminarCosto({{ $costo->idcos }})"
                            title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
