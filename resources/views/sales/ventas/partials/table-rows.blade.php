@foreach ($ventas as $venta)
    <tr>
        <td><strong>#{{ $venta->idven }}</strong></td>
        <td>{{ $venta->cliente->nombrecli }}</td>
        <td>{{ $venta->empleado->nombreemp }}</td>
        <td>{{ $venta->fechaven->format('Y/m/d') }}</td>
        <td><strong>${{ number_format($venta->totalpagoven, 2) }}</strong></td>
        @if (Auth::user()->hasAnyPermission(['ventas.edit', 'ventas.renew', 'ventas.sendInvoice', 'ventas.destroy']))
            <td>
                <div class="action-buttons">
                    <!-- Ver detalles -->
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                        data-bs-target="#ventaDetalleModal{{ $venta->idven }}"
                        title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>

                    @if (Auth::user()->hasPermissionTo('ventas.edit'))
                        <a href="{{ route('ventas.edit', $venta->idven) }}"
                           class="btn btn-warning btn-sm"
                           title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                    @endif

                    @if (Auth::user()->hasPermissionTo('ventas.renew'))
                        <a href="{{ route('ventas.renew', ['idcli' => $venta->cliente->idcli, 'idven' => $venta->idven]) }}"
                           class="btn btn-success btn-sm"
                           title="Renovar">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    @endif

                    @if (Auth::user()->hasPermissionTo('ventas.destroy'))
                        <form action="{{ route('ventas.destroy', $venta->idven) }}"
                              method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('¿Estás seguro de eliminar esta venta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    @endif

                    @if (Auth::user()->hasPermissionTo('ventas.sendInvoice'))
                        <form action="{{ route('ventas.sendInvoice', $venta->idven) }}"
                              method="POST"
                              style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" title="Enviar factura">
                                <i class="fas fa-envelope"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
