@extends('layouts.static')
@section('styles')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
    <style>
        .fc-event-title {
            white-space: normal;
            /* Permitir saltos de línea */
            word-wrap: break-word;
            /* Ajustar palabras largas */
        }

        .fc-daygrid-event {
            height: auto !important;
            /* Ajustar la altura automáticamente */
        }
    </style>
@endsection
@section('title', 'Calendario de Cuentas')
@section('h1', 'Calendario de Cuentas')
@section('breadcrumb')
    <a href="{{ route('cuentas') }}">Cuentas</a>
@endsection
@section('breadcrumb2')
    Calendario de Cuentas
@endsection
@section('introduccion')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    Aquí puedes ver el calendario de cuentas. Puedes agregar eventos al calendario haciendo clic en la fecha deseada.
    Asegúrate de ingresar todos los campos correctamente.
    En esta vista, se agrega una cuenta a la tabla cuentas.
@endsection
@section('content')
    <div class="container">
        <div id="calendar"></div>
    </div>

@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            // Obtener los datos desde el backend
            var cuentas = @json($cuentas);
            var usuarios = @json($usuarios);
            var tareas = @json($tareas);

            // Formatear los datos como eventos para el calendario
            var eventos = [];

            // Agregar cuentas al calendario
            cuentas.forEach(function(cuenta) {
                eventos.push({
                    title: 'Cuenta: ' + cuenta.idcue,
                    start: cuenta.fechavencue,
                    allDay: true,
                    color: new Date(cuenta.fechavencue) < new Date() ? 'red' :
                    'yellow', // Rojo si está vencido
                    textColor: new Date(cuenta.fechavencue) < new Date() ? 'white' :
                    'black', // Texto negro si amarillo
                    extendedProps: {
                        type: 'cuenta',
                        id: cuenta.idcue,
                        name: cuenta.usuariocue,
                    }
                });
            });

            // Agregar usuarios al calendario
            usuarios.forEach(function(usuario) {
                eventos.push({
                    title: 'Usuario: ' + usuario.nombre_cliente,
                    start: usuario.fecha_vencimiento,
                    allDay: true,
                    color: new Date(usuario.fecha_vencimiento) < new Date() ? 'red' :
                    'green', // Rojo si está vencido
                    textColor: 'white',
                    extendedProps: {
                        type: 'usuario',
                        id: usuario.idcli,
                        name: usuario.nombre_cliente,
                        cuenta: usuario.idcue,
                        perfil: usuario.perfil,
                    }
                });
            });

            // Agregar tareas al calendario
            tareas.forEach(function(tarea) {
                eventos.push({
                    title: 'Tarea: ' + tarea.nombretarea,
                    start: tarea.fechalimit,
                    allDay: true,
                    color: new Date(tarea.fechalimit) < new Date() ? 'red' :
                    'blue', // Rojo si está vencido
                    textColor: 'white',
                    extendedProps: {
                        type: 'tarea',
                        id: tarea.id,
                        name: tarea.nombretarea,
                    }
                });
            });

            // Inicializar el calendario
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Vista mensual
                locale: 'es', // Idioma español
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: eventos, // Cargar los eventos en el calendario
                // Personalizar el contenido de las celdas del calendario
                dayCellContent: function(dayCell) {
                    // Filtrar eventos que coincidan con la fecha de la celda
                    var date = dayCell.date.toISOString().split('T')[
                    0]; // Obtener la fecha en formato YYYY-MM-DD
                    var cuentasDelDia = eventos.filter(evento => evento.start === date && evento
                        .extendedProps.type === 'cuenta');
                    var usuariosDelDia = eventos.filter(evento => evento.start === date && evento
                        .extendedProps.type === 'usuario');
                    var tareasDelDia = eventos.filter(evento => evento.start === date && evento
                        .extendedProps.type === 'tarea');

                    // Crear el contenido adicional
                    var contenido = `
            <div style="font-size: 11px; color: black;">
                ${cuentasDelDia.length > 0 ? `Cuentas: ${cuentasDelDia.length}<br>` : ''}
                ${usuariosDelDia.length > 0 ? `Usuarios: ${usuariosDelDia.length}<br>` : ''}
                ${tareasDelDia.length > 0 ? `Tareas: ${tareasDelDia.length}` : ''}
            </div>
        `;

                    // Retornar el contenido adicional
                    return {
                        html: dayCell.dayNumberText + contenido
                    };
                },
                // Evento al hacer clic en un evento
                eventClick: function(info) {
                    var eventType = info.event.extendedProps.type;
                    var eventId = info.event.extendedProps.id;
                    var eventName = info.event.extendedProps.name || '';
                    var eventAccount = info.event.extendedProps.cuenta || '';
                    var eventProfile = info.event.extendedProps.perfil || '';

                    // Mostrar opciones según el tipo de evento
                    if (eventType === 'cuenta') {
                        Swal.fire({
                            title: 'Opciones para la cuenta ' + eventId + ' (' + eventName +
                                ')',
                            html: `
                            <button class="btn btn-primary" onclick="renovarCuenta(${eventId})">Renovar</button>
                            <button class="btn btn-warning" onclick="editarCuenta(${eventId})">Editar</button>
                            <button class="btn btn-danger" onclick="eliminarCuenta(${eventId})">Eliminar</button>
                        `,
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    } else if (eventType === 'usuario') {
                        Swal.fire({
                            title: 'Opciones para el usuario ' + eventName + ' (' +
                                eventAccount + ' Perfil: ' + eventProfile + ')',
                            html: `
                            <button class="btn btn-primary" onclick="renovarUsuario(${eventId})">Renovar</button>
                            <button class="btn btn-warning" onclick="editarUsuario(${eventId})">Editar</button>
                            <button class="btn btn-danger" onclick="eliminarUsuario(${eventId})">Eliminar</button>
                        `,
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    } else if (eventType === 'tarea') {
                        Swal.fire({
                            title: 'Opciones para la tarea: ' + eventName,
                            html: `
                            <button class="btn btn-success" onclick="completarTarea(${eventId})">Completar</button>
                        `,
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    }
                }
            });

            calendar.render();
        });

        // Funciones para manejar las acciones
        function renovarCuenta(id) {
            window.location.href = `/admin/cuentas/${id}/renew`;
        }

        function editarCuenta(id) {
            window.location.href = `/admin/cuentas/${id}/edit`;
        }

        function eliminarCuenta(id) {
            if (confirm('¿Estás seguro de eliminar esta cuenta?')) {
                fetch(`/admin/cuentas/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => {
                    if (response.ok) {
                        alert('Cuenta eliminada correctamente.');
                        location.reload();
                    } else {
                        alert('Error al eliminar la cuenta.');
                    }
                });
            }
        }

        function renovarUsuario(id) {
            window.location.href = `/admin/usuarios/${id}/renew`;
        }

        function editarUsuario(id) {
            window.location.href = `/admin/usuarios/${id}/change`;
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                fetch(`/admin/usuarios/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => {
                    if (response.ok) {
                        alert('Usuario eliminado correctamente.');
                        location.reload();
                    } else {
                        alert('Error al eliminar el usuario.');
                    }
                });
            }
        }

        function completarTarea(id) {
            fetch(`/admin/tareas/${id}/completar`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => {
                if (response.ok) {
                    alert('Tarea completada correctamente.');
                    location.reload();
                } else {
                    alert('Error al completar la tarea.');
                }
            });
        }
    </script>
@endsection
