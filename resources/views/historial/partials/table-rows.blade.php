@foreach ($historial as $accion)
    <tr>
        <td><strong>{{ $accion->id }}</strong></td>
        <td>{{ $accion->accion }}</td>
        <td>{{ $accion->descripcion }}</td>
        <td>
            @if ($accion->empleado)
                {{ $accion->empleado->nombreemp }}
            @else
                <span class="text-muted">No asignado</span>
            @endif
        </td>
        <td>{{ $accion->created_at->format('Y/m/d H:i') }}</td>
    </tr>
@endforeach
