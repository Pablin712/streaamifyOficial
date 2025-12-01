@foreach ($costos as $costo)
    <tr>
        <td>{{ $costo->idcos }}</td>
        <td>{{ $costo->cuenta->idcue }} - {{ $costo->cuenta->usuariocue }}</td>
        <td>{{ $costo->fechacos }}</td>
        <td>{{ $costo->descripcioncos }}</td>
        <td>${{ number_format($costo->montocos, 2) }}</td>
        @if (Auth::user()->hasAnyPermission(['costos.update', 'costos.destroy']))
            <td>
                <div class="action-buttons">
                    @if (Auth::user()->hasPermissionTo('costos.update'))
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#editarCostoModal" data-id="{{ $costo->idcos }}"
                            data-idcue="{{ $costo->idcue }}" data-descripcioncos="{{ $costo->descripcioncos }}"
                            data-montocos="{{ $costo->montocos }}" data-fechacos="{{ $costo->fechacos }}"
                            title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif
                    @if (Auth::user()->hasPermissionTo('costos.destroy'))
                        <form action="{{ route('costos.destroy', $costo->idcos) }}" method="POST"
                            style="display: inline;"
                            onsubmit="return confirm('¿Estás seguro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
