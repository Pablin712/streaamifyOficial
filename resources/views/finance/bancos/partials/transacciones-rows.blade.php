@forelse ($transacciones as $transaccion)
    <tr>
        <td>{{ $transaccion->id }}</td>
        <td>{{ $transaccion->banco->nombreban ?? 'N/A' }}</td>
        <td>
            <span class="badge bg-{{ $transaccion->tipo === 'ingreso' ? 'success' : 'danger' }}">
                {{ ucfirst($transaccion->tipo) }}
            </span>
        </td>
        <td>${{ number_format($transaccion->monto_anterior, 2) }}</td>
        <td>${{ number_format($transaccion->monto_actualizado, 2) }}</td>
        <td class="fw-bold text-{{ $transaccion->tipo === 'ingreso' ? 'success' : 'danger' }}" style="font-size: 1.1em;">
            ${{ number_format($transaccion->monto_transaccion, 2) }}
        </td>
        <td>{{ $transaccion->referencia ?? '-' }}</td>
        <td>{{ \Carbon\Carbon::parse($transaccion->fecha)->format('d/m/Y H:i') }}</td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted">No hay transacciones registradas</td>
    </tr>
@endforelse
