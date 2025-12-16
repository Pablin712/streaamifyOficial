@extends('layouts.static')

@section('title', 'Editar Venta')

@section('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 Dark Mode -->
    <link href="{{ asset('css/select2-dark-mode.css') }}" rel="stylesheet" />
@endsection

@section('h1', 'Editar Venta')
@section('breadcrumb')
    <a href="{{ route('ventas') }}">Ventas</a>
@endsection
@section('breadcrumb2')
    Editar Venta
@endsection
@section('introduccion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    En esta vista, puedes editar una venta existente y sus detalles.
@endsection

@section('content')
    <div class="container">
        <!-- Alerta Informativa -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Atención!</strong> Recuerda que debes presionar el botón <strong>"Actualizar Venta"</strong>
            al final del formulario para guardar todos los cambios realizados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <h2>Editar Venta</h2>
        <form id="form-venta" method="POST" action="{{ route('ventas.update', $venta->idven) }}">
            @csrf
            @method('PUT')
            <div class="form-group mb-3">
                <label for="idven">Factura</label>
                <input type="text" class="form-control" name="idventa" value="{{ $venta->idven }}" disabled>
            </div>
            <div class="form-group mb-3">
                <label for="idcli">Cliente</label>
                <input type="text" class="form-control" value="{{ $venta->cliente->nombrecli }}" disabled>
                <input type="hidden" name="idcli" value="{{ $venta->idcli }}">
            </div>

            <!-- Checkbox: ¿Se pagó? -->
            <div class="form-group mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="se_pago" id="se_pago_edit" value="1"
                           {{ $venta->transaccion_id ? 'checked' : '' }}>
                    <label class="form-check-label" for="se_pago_edit">
                        ¿Se pagó?
                    </label>
                </div>
            </div>

            <!-- Campo Banco (visible solo si se marcó como pagado) -->
            <div class="form-group mb-3" id="banco_field_edit">
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
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalles">
                        @foreach ($venta->detalles_venta as $detalle)
                            <tr>
                                <td>{{ $cuenta = $detalle->perfil->idcue }}</td>
                                <td>{{ $perfil = $detalle->perfil->numeroper }}</td>
                                <td>{{ $descripcion = $detalle->descripciondet }}</td>
                                <td>{{ $fechaVencimiento = $detalle->fechavendet }}</td>
                                <td>${{ $monto = number_format($detalle->montodet, 2) }}</td>
                                <td>
                                    <span @php
$estado = $detalle->activodet @endphp
                                        class="estado-{{ $detalle->iddet }} badge
                                        @if ($estado) bg-success @else bg-danger @endif">
                                        @if ($estado)
                                            Activa
                                        @else
                                            Vencida
                                        @endif
                                    </span>
                                    <!-- Botón para cambiar estado -->
                                    <button type="button" class="btn btn-dark btn-sm toggleEstadoBtn"
                                        data-id="{{ $detalle->iddet }}" data-estado="{{ $detalle->activodet }}">
                                        @if ($estado)
                                            <i class="fas fa-toggle-on fa-xs"></i>
                                        @else
                                            <i class="fas fa-toggle-off fa-xs"></i>
                                        @endif
                                    </button>
                                </td>
                                <td>
                                    <!-- Botón Editar -->
                                    <button type="button" class="btn btn-warning btn-sm editarDetalleBtn"
                                        data-cuenta="{{ $cuenta }}" data-perfil="{{ $perfil }}"
                                        data-descripcion="{{ $descripcion }}"
                                        data-fechavencimiento="{{ $fechaVencimiento }}" data-monto="{{ $monto }}"
                                        data-id="{{ $detalle->iddet }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm eliminarDetalleBtn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-end">
                    <strong>Total Venta: $<span
                            id="total-venta">{{ number_format($venta->totalpagoven, 2) }}</span></strong>
                </div>
            </div>

            <!-- Campo oculto para enviar los detalles de la venta -->
            <input type="hidden" name="detalles_venta" id="detalles_venta">

            <input type="hidden" name="idemp" id="idemp" value="1">{{-- {{ auth()->user()->id }} --}}
            <button type="submit" class="btn btn-primary mt-4" id="registrar-venta">Actualizar Venta</button>
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
        let detalleEditando = null; // Variable para guardar el detalle que se está editando

        // Cambiar estado (mantiene la funcionalidad existente)
        document.querySelectorAll('.toggleEstadoBtn').forEach(button => {
            button.addEventListener('click', function() {
                const detalleId = this.getAttribute('data-id');
                const estado = this.getAttribute('data-estado') === '1';

                // Cambiar el estado visualmente
                const badge = document.querySelector(`.estado-${detalleId}`);
                if (estado) {
                    badge.classList.remove('bg-success');
                    badge.classList.add('bg-danger');
                    badge.innerText = 'Vencida';
                } else {
                    badge.classList.remove('bg-danger');
                    badge.classList.add('bg-success');
                    badge.innerText = 'Activa';
                }

                // Actualizamos el atributo "data-estado" para reflejar el cambio
                this.setAttribute('data-estado', estado ? '0' : '1');

                // Cambiar el ícono del botón también
                const icon = this.querySelector('i');
                if (estado) {
                    icon.classList.remove('fa-toggle-on');
                    icon.classList.add('fa-toggle-off');
                } else {
                    icon.classList.remove('fa-toggle-off');
                    icon.classList.add('fa-toggle-on');
                }
            });
        });

        // Función para abrir el modal de edición y cargar los datos
        $(document).on('click', '.editarDetalleBtn', function() {
            const cuenta = $(this).closest('tr').find('td').eq(0).text();
            const perfil = $(this).closest('tr').find('td').eq(1).text();
            const descripcion = $(this).closest('tr').find('td').eq(2).text();
            const fechaVencimiento = $(this).closest('tr').find('td').eq(3).text();
            const monto = parseFloat($(this).closest('tr').find('td').eq(4).text().replace('$', '').trim());

            // Guardar la referencia a la fila que se está editando
            detalleEditando = $(this).closest('tr');

            // Cargar los valores en los campos del modal
            $('#editarSelectCuenta').val(cuenta);
            $('#editarSelectPerfil').val(perfil);
            $('#editarDescripcion').val(descripcion);
            $('#editarFechaVencimiento').val(fechaVencimiento);
            $('#editarMonto').val(monto);

            // Abrir el modal de edición usando Alpine.js
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editar-detalle-modal' }));
        });

        // Guardar cambios en el detalle editado
        $('#guardarCambiosDetalleBtn').on('click', function() {
            if (!detalleEditando) return; // Si no hay un detalle cargado, salir

            // Obtener los valores del modal
            const descripcion = $('#editarDescripcion').val();
            const fechaVencimiento = $('#editarFechaVencimiento').val();
            const nuevoMonto = parseFloat($('#editarMonto').val());

            // Validar los campos
            if (descripcion && fechaVencimiento && !isNaN(nuevoMonto)) {
                // Obtener el monto anterior de la fila editada
                const montoAnterior = parseFloat(detalleEditando.find('td').eq(4).text().replace('$', '').trim());

                // Actualizar los datos en la fila correspondiente
                detalleEditando.find('td').eq(2).text(descripcion);
                detalleEditando.find('td').eq(3).text(fechaVencimiento);
                detalleEditando.find('td').eq(4).text(`$${nuevoMonto.toFixed(2)}`);

                // Recalcular el total de la venta (ajustando con la diferencia entre el nuevo y el anterior)
                const totalVentaActual = parseFloat($('#total-venta').text());
                const nuevoTotal = totalVentaActual - montoAnterior + nuevoMonto;
                $('#total-venta').text(nuevoTotal.toFixed(2));

                // Cerrar el modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editar-detalle-modal' }));
            } else {
                alert('Por favor, completa todos los campos correctamente.');
            }
        });
        // Manejo para agregar detalles (mantiene la funcionalidad existente)
        $('#guardarDetalleBtn').on('click', function() {
            var cuenta = $('#selectCuenta').val();
            var perfil = $('#selectPerfil').val();
            var fechaVencimiento = $('#fechaVencimiento').val();
            var descripcion = $('#descripcion').val();
            var monto = parseFloat($('#monto').val());
            var estado = true;
            if (cuenta && perfil && fechaVencimiento && descripcion && monto) {
                var totalVenta = parseFloat($('#total-venta').text()) + monto;

                var nuevaFila = `<tr>
                <td>${cuenta}</td>
                <td>${perfil}</td>
                <td>${descripcion}</td>
                <td>${fechaVencimiento}</td>
                <td>$${monto.toFixed(2)}</td>
                <td>
                    <span class="estado-${cuenta} badge ${estado ? 'bg-success' : 'bg-danger'}">
                        ${estado ? 'Activa' : 'Vencida'}
                    </span>
                    <button type="button" class="btn btn-dark btn-sm toggleEstadoBtn"
                        data-id="${cuenta}" data-estado="${estado ? '1' : '0'}">
                        <i class="fas ${estado ? 'fa-toggle-on' : 'fa-toggle-off'} fa-xs"></i>
                    </button>
                </td>
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
                $('#total-venta').text(totalVenta.toFixed(2));

                $('#selectCuenta').val('');
                $('#selectPerfil').val('');
                $('#fechaVencimiento').val('');
                $('#descripcion').val('');
                $('#monto').val('');

                // Cerrar modal usando Alpine.js
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'agregar-detalle-modal' }));
            } else {
                alert('Por favor complete todos los campos.');
            }
        });

        // Eliminar una fila de detalles (mantiene la funcionalidad existente)
        $('#tabla-detalles').on('click', '.eliminarDetalleBtn', function() {
            var montoEliminado = parseFloat($(this).closest('tr').find('td').eq(4).text().replace('$', ''));
            var totalVenta = parseFloat($('#total-venta').text()) - montoEliminado;
            $(this).closest('tr').remove();
            $('#total-venta').text(totalVenta.toFixed(2));
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
        // Enviar los detalles al backend (mantiene la funcionalidad existente)
        document.getElementById('form-venta').addEventListener('submit', function(event) {
            event.preventDefault();

            let detalles = [];
            document.querySelectorAll('#tabla-detalles tr').forEach(function(row) {
                let cuenta = row.cells[0].innerText;
                let perfil = row.cells[1].innerText;
                let descripcion = row.cells[2].innerText;
                let fechaVencimiento = row.cells[3].innerText;
                let monto = parseFloat(row.cells[4].innerText.replace('$', '').trim());
                let estadoBadge = row.querySelector('.toggleEstadoBtn');
                let estado = estadoBadge.getAttribute('data-estado') === '1';

                detalles.push({
                    cuenta,
                    perfil,
                    descripcion,
                    fecha_vencimiento: fechaVencimiento,
                    monto,
                    estado
                });
            });

            document.getElementById('detalles_venta').value = JSON.stringify(detalles);
            this.submit();
        });

        // Toggle del campo Banco según checkbox "¿Se pagó?"
        document.addEventListener('DOMContentLoaded', function() {
            const sePagoCheckboxEdit = document.getElementById('se_pago_edit');
            const bancoFieldEdit = document.getElementById('banco_field_edit');
            const bancoSelectEdit = document.getElementById('banco_id');

            function toggleBancoFieldEdit() {
                if (sePagoCheckboxEdit && sePagoCheckboxEdit.checked) {
                    bancoFieldEdit.style.display = 'block';
                    bancoSelectEdit.required = true;
                } else {
                    bancoFieldEdit.style.display = 'none';
                    bancoSelectEdit.required = false;
                    bancoSelectEdit.value = '';
                }
            }

            if (sePagoCheckboxEdit) {
                sePagoCheckboxEdit.addEventListener('change', toggleBancoFieldEdit);
                toggleBancoFieldEdit(); // Ejecutar al cargar
            }
        });
    </script>
@endsection
