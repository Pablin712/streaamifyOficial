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
                    <button onclick="openViewDetailsModal('{{ $venta->idven }}')"
                            class="btn btn-info btn-sm"
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
                        <button onclick="openDeleteModal('{{ $venta->idven }}', '{{ addslashes($venta->cliente->nombrecli) }}', '{{ number_format($venta->totalpagoven, 2) }}')"
                                class="btn btn-danger btn-sm"
                                title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    @endif

                    @if (Auth::user()->hasPermissionTo('ventas.sendInvoice'))
                        <button onclick="openSendInvoiceModal('{{ $venta->idven }}', '{{ addslashes($venta->cliente->nombrecli) }}', '{{ $venta->cliente->email ?? '' }}', '{{ number_format($venta->totalpagoven, 2) }}')"
                                class="btn btn-secondary btn-sm"
                                title="Enviar factura">
                            <i class="fas fa-envelope"></i>
                        </button>
                    @endif
                </div>
            </td>
        @endif
    </tr>
@endforeach
