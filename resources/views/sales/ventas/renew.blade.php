@extends('layouts.static')
@section('title', 'Renovar Venta')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection

@section('h1', 'Renovar Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Renovar Venta
@endsection
@section('content')
    <div class="container">
        <!-- Alerta Informativa -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Recordatorio:</strong> Esta renovación no se guardará hasta que presiones el botón
            <strong>"Registrar Venta"</strong> al final de la página.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <h2>Renovar Venta</h2>
        <form id="form-venta" method="POST" action="{{ route('ventas.storeRenew') }}">
            @csrf
            <div class="form-group mb-3">

                <div class="form-group mb-3">
                    <label for="idcli">Cliente</label>
                    <input type="text" class="form-control" value="{{ $venta->cliente->nombrecli }}" readonly>
                    <!-- Campo oculto para enviar el idcli al servidor -->
                    <input type="hidden" name="idcli" id="idcli" value="{{ $venta->cliente->idcli }}">
                </div>
                <input type="hidden" name="idvenPasado" value="{{ $venta->idven }}">

            </div>

            <!-- Checkbox: ¿Se pagó? -->
            <div class="form-group mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="se_pago" id="se_pago_renew" value="1" checked>
                    <label class="form-check-label" for="se_pago_renew">
                        ¿Se pagó?
                    </label>
                </div>
            </div>

            <!-- Método de pago -->
            <div id="pago_fields_renew">
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold">Método de pago</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo_pago" id="mp_banco_renew" value="banco" checked>
                            <label class="form-check-label" for="mp_banco_renew">
                                <i class="fas fa-university me-1 text-primary"></i>Banco
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo_pago" id="mp_saldo_renew" value="saldo">
                            <label class="form-check-label" for="mp_saldo_renew">
                                <i class="fas fa-wallet me-1 text-success"></i>Saldo del cliente
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Banco -->
                <div class="form-group mb-3" id="banco_field_renew">
                    <label for="banco_id">Banco <span class="text-danger">*</span></label>
                    <select name="banco_id" id="banco_id" class="form-control searchable-select"
                            data-placeholder="Seleccione un banco...">
                        <option value="">-- Selecciona un Banco --</option>
                        @foreach ($bancos as $banco)
                            <option value="{{ $banco->idban }}"
                                {{ ($venta->transaccion && $venta->transaccion->banco_id == $banco->idban) ? 'selected' : '' }}>
                                {{ $banco->nombreban }} ({{ ucfirst($banco->tipoban) }}) - ${{ number_format($banco->monto, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Saldo del cliente -->
                <div class="form-group mb-3" id="saldo_field_renew" style="display:none;">
                    @php
                        $saldoCliente = $venta->cliente->saldo ?? 0;
                        $suficiente   = $saldoCliente >= $totalVenta;
                    @endphp
                    <div class="alert {{ $suficiente ? 'alert-success' : 'alert-warning' }}">
                        <i class="fas fa-wallet me-2"></i>
                        Saldo disponible: <strong>${{ number_format($saldoCliente, 2) }}</strong>
                        &nbsp;|&nbsp; Total renovación: <strong>${{ number_format($totalVenta, 2) }}</strong>
                        @if(!$suficiente)
                            <br><small class="text-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Saldo insuficiente — faltan <strong>${{ number_format($totalVenta - $saldoCliente, 2) }}</strong>
                            </small>
                        @else
                            <br><small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>Saldo suficiente
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4>Detalles de Venta</h4>
                <button type="button" class="btn btn-success"
                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'agregar-detalle-modal' }))">
                    <i class="fas fa-plus-circle me-1"></i> Agregar Detalle
                </button>

                <table class="table table-bordered mt-3" id="detalles-venta">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Perfil</th>
                            <th>Descripción</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        @foreach ($detalles as $detalle)
                            @php
                                $cuentaId = $detalle->perfil?->cuenta?->idcue;
                                $perfilNumero = $detalle->perfil?->numeroper;
                                $detalleValido = !empty($cuentaId) && !empty($perfilNumero);
                            @endphp
                            <tr>
                                <td>{{ $cuentaId ?? 'Cuenta eliminada' }}</td>
                                <td>{{ $perfilNumero ?? 'Perfil eliminado' }}</td>
                                <td>Renovacion Cuenta</td>
                                <td>{{ $detalle->fechavendet_suma }}</td>
                                <td>${{ number_format($detalle->montodet, 2) }}</td>
                                <td data-detalle-valido="{{ $detalleValido ? 1 : 0 }}">
                                    @if ($detalleValido)
                                        <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"
                                            data-cuenta="{{ $cuentaId }}"
                                            data-perfil="{{ $perfilNumero }}"
                                            data-descripcion="Renovacion Cuenta"
                                            data-fechavencimiento="{{ $detalle->fechavendet_suma }}"
                                            data-monto="{{ $detalle->montodet }}" data-id="{{ $detalle->iddet }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-warning text-dark">Detalle incompleto</span>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Los detalles se agregarán aquí mediante JavaScript -->
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span id="total-venta">{{ number_format($totalVenta, 2) }}</span></strong>
                </div>

            </div>
            <!-- Campo oculto para enviar los detalles de la venta -->
            <input type="hidden" name="detalles_venta" id="detalles_venta">
            {{-- Campo oculto para enviar la información del empleado que registró la venta (CON LOGIN) --}}
            <input type="hidden" name="idemp" id="idemp" value={{ Auth::user()->idemp }}>
            <button type="submit" class="btn btn-primary mt-4" id="registrar-venta">Registrar Venta</button>
        </form>
    </div>
    <br>

    @include('shared.modals.venta-agregar-detalle')
    @include('shared.modals.venta-editar-detalle')
@endsection
@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
    <!-- jQuery (debe cargarse primero) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 (debe cargarse después de jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Inicializador de searchable-selects -->
    <script src="{{asset('js/searchable-select.js')}}"></script>

    <script>
        let detalleEditando = null; // Variable para rastrear la fila en edición

        // Abrir el modal de edición y cargar los datos
        $(document).on('click', '.editarDetalleBtn', function() {
            const cuenta = $(this).closest('tr').find('td').eq(0).text();
            const perfil = $(this).closest('tr').find('td').eq(1).text();
            const descripcion = $(this).closest('tr').find('td').eq(2).text();
            const fechaVencimiento = $(this).closest('tr').find('td').eq(3).text();
            const monto = parseFloat($(this).closest('tr').find('td').eq(4).text().replace('$', '').trim());

            // Guardar la referencia de la fila que se está editando
            detalleEditando = $(this).closest('tr');

            // Cargar los valores en el modal
            $('#editarSelectCuenta').val(cuenta);
            $('#editarSelectPerfil').val(perfil);
            $('#editarDescripcion').val(descripcion);
            $('#editarFechaVencimiento').val(fechaVencimiento);
            $('#editarMonto').val(monto);

            // Mostrar el modal usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-detalle-modal' }));
        });

        // Guardar cambios en el detalle editado
        $('#guardarCambiosDetalleBtn').on('click', function() {
            if (!detalleEditando) return; // Si no hay detalle cargado, salir

            // Obtener los valores del modal
            const descripcion = $('#editarDescripcion').val();
            const fechaVencimiento = $('#editarFechaVencimiento').val();
            const monto = parseFloat($('#editarMonto').val());

            // Validar los campos
            if (descripcion && fechaVencimiento && !isNaN(monto)) {
                // Actualizar los datos en la fila
                detalleEditando.find('td').eq(2).text(descripcion);
                detalleEditando.find('td').eq(3).text(fechaVencimiento);
                detalleEditando.find('td').eq(4).text(`$${monto.toFixed(2)}`);

                // Actualizar el total de la venta
                actualizarTotalVenta();

                // Cerrar el modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-detalle-modal' }));
            } else {
                alert('Por favor completa todos los campos correctamente.');
            }
        });

        // Agregar un detalle nuevo
        $('#guardarDetalleBtn').on('click', function() {
            const cuenta = $('#selectCuenta').val();
            const perfil = $('#selectPerfil').val();
            const fechaVencimiento = $('#fechaVencimiento').val();
            const descripcion = $('#descripcion').val();
            const monto = parseFloat($('#monto').val());

            if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
                const nuevaFila = `<tr>
                <td>${cuenta}</td>
                <td>${perfil}</td>
                <td>${descripcion}</td>
                <td>${fechaVencimiento}</td>
                <td>$${monto.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

                $('#tabla-detalles').append(nuevaFila);

                // Actualizar el total de la venta
                actualizarTotalVenta();

                // Limpiar los campos del modal de agregar
                $('#selectCuenta').val('');
                $('#selectPerfil').val('');
                $('#fechaVencimiento').val('');
                $('#descripcion').val('');
                $('#monto').val('');

                // Cerrar el modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }));
            } else {
                alert('Por favor complete todos los campos.');
            }
        });

        // Eliminar un detalle
        $(document).on('click', '.eliminarDetalleBtn', function() {
            const montoEliminado = parseFloat(
                $(this).closest('tr').find('td').eq(4).text().replace('$', '').trim()
            );

            $(this).closest('tr').remove();

            // Actualizar el total de la venta
            actualizarTotalVenta();
        });

        // Función para calcular y actualizar el total de la venta
        function actualizarTotalVenta() {
            let total = 0;
            $('#tabla-detalles tr').each(function() {
                const monto = parseFloat($(this).find('td').eq(4).text().replace('$', '').trim());
                if (!isNaN(monto)) {
                    total += monto;
                }
            });
            $('#total-venta').text(total.toFixed(2));
        }

        // Enviar los detalles al backend
        $('#form-venta').on('submit', function(event) {
            event.preventDefault(); // Evitar el envío nativo: se envía explícitamente con this.submit() más abajo

            const submitBtn = document.getElementById('registrar-venta');
            if (submitBtn.disabled) {
                return; // Ya se envió, evita doble clic / doble submit
            }

            const detalles = [];
            $('#tabla-detalles tr').each(function() {
                const detalleValido = parseInt($(this).find('td').eq(5).attr('data-detalle-valido') || '1', 10) === 1;
                if (!detalleValido) {
                    return;
                }

                const cuenta = $(this).find('td').eq(0).text();
                const perfil = $(this).find('td').eq(1).text();
                const descripcion = $(this).find('td').eq(2).text();
                const fechaVencimiento = $(this).find('td').eq(3).text();
                const monto = parseFloat($(this).find('td').eq(4).text().replace('$', '').trim());

                if (cuenta && perfil && descripcion && fechaVencimiento && monto) {
                    detalles.push({
                        cuenta,
                        perfil,
                        descripcion,
                        fecha_vencimiento: fechaVencimiento,
                        monto,
                    });
                }
            });

            if (detalles.length === 0) {
                alert('No hay detalles válidos para renovar. Elimina los incompletos y agrega nuevos detalles.');
                return;
            }

            $('#detalles_venta').val(JSON.stringify(detalles));
            submitBtn.disabled = true;
            this.submit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sePagoCheckbox = document.getElementById('se_pago_renew');
            const pagoFields     = document.getElementById('pago_fields_renew');
            const bancoField     = document.getElementById('banco_field_renew');
            const saldoField     = document.getElementById('saldo_field_renew');
            const bancoSelect    = document.getElementById('banco_id');

            // Cambiar método de pago
            document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const esBanco = this.value === 'banco';
                    bancoField.style.display = esBanco ? 'block' : 'none';
                    bancoSelect.required = esBanco;
                    if (!esBanco) bancoSelect.value = '';
                    saldoField.style.display = esBanco ? 'none' : 'block';
                });
            });

            function togglePagoFields() {
                const sePago = sePagoCheckbox?.checked;
                pagoFields.style.display = sePago ? 'block' : 'none';
                if (!sePago) {
                    bancoSelect.required = false;
                } else {
                    bancoSelect.required = document.getElementById('mp_banco_renew')?.checked ?? true;
                }
            }

            if (sePagoCheckbox) {
                sePagoCheckbox.addEventListener('change', togglePagoFields);
                togglePagoFields();
            }
        });
    </script>
@endsection
