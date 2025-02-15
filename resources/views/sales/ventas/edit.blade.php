@extends('layouts.static')

@section('title', 'Editar Venta')

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

            <div class="mt-4">
                <h4>Detalles de Venta</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarDetalleModal">
                    Agregar Detalle
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
                                        data-id="{{ $detalle->iddet }}" data-bs-toggle="modal"
                                        data-bs-target="#editarDetalleModal">
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

    <!-- Modal para agregar detalle -->
    <div class="modal fade" id="agregarDetalleModal" tabindex="-1" aria-labelledby="agregarDetalleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarDetalleModalLabel">Agregar Detalle a la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formDetalle">
                        <!-- Select Cuenta -->
                        <div class="mb-3">
                            <label for="selectCuenta" class="form-label">Cuenta</label>
                            <select class="form-select" id="selectCuenta" required>
                                <option value="">Seleccione una cuenta</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->idcue }}">
                                        {{ $cuenta->idcue }}: Oc: {{ $cuenta->usuarios_activos }} ::
                                        @foreach ($cuenta->perfiles as $perfil)
                                            P{{ $perfil->numeroper }}: {{ $perfil->usuarios_activos }}&nbsp;&nbsp;
                                        @endforeach
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Perfil -->
                        <div class="mb-3">
                            <label for="selectPerfil" class="form-label">Perfil</label>
                            <input type="number" class="form-control" id="selectPerfil" min="1" max="7"
                                required>
                        </div>

                        <!-- Fecha de vencimiento -->
                        <div class="mb-3">
                            <label for="fechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="fechaVencimiento" required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" required>
                        </div>

                        <!-- Monto -->
                        <div class="mb-3">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="monto" step="0.01" min="0"
                                required>
                        </div>

                        <button type="button" class="btn btn-primary" id="guardarDetalleBtn">Guardar Detalle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar detalle -->
    <div class="modal fade" id="editarDetalleModal" tabindex="-1" aria-labelledby="editarDetalleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarDetalleModalLabel">Editar Detalle de la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarDetalle">
                        <!-- Cuenta -->
                        <div class="mb-3">
                            <label for="editarCuenta" class="form-label">Cuenta</label>
                            <input type="text" class="form-control" id="editarCuenta" readonly>
                        </div>

                        <!-- Perfil -->
                        <div class="mb-3">
                            <label for="editarPerfil" class="form-label">Perfil</label>
                            <input type="text" class="form-control" id="editarPerfil" readonly>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="editarDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editarDescripcion" rows="2" required></textarea>
                        </div>

                        <!-- Fecha de Vencimiento -->
                        <div class="mb-3">
                            <label for="editarFechaVencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="editarFechaVencimiento" required>
                        </div>

                        <!-- Monto -->
                        <div class="mb-3">
                            <label for="editarMonto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="editarMonto" step="0.01" min="0"
                                required>
                        </div>

                        <button type="button" class="btn btn-primary" id="guardarCambiosDetalleBtn">Guardar
                            Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pie')
    <p>¿No deseas agregar una cuenta al stock? Vuelve a la página de listado:</p>
    <a href="{{ route('ventas') }}" class="btn btn-secondary">Volver a Ventas</a>
@endsection

@section('scripts')
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
            $('#editarCuenta').val(cuenta);
            $('#editarPerfil').val(perfil);
            $('#editarDescripcion').val(descripcion);
            $('#editarFechaVencimiento').val(fechaVencimiento);
            $('#editarMonto').val(monto);

            // Abrir el modal de edición
            $('#editarDetalleModal').modal('show');
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

                // Cerrar el modal
                $('#editarDetalleModal').modal('hide');
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

                $('#agregarDetalleModal').modal('hide');
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
    </script>
@endsection
