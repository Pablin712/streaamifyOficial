@extends('layouts.static')
@section('styles')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
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
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            // Obtener las cuentas desde el backend
            var cuentas = @json($cuentas);

            // Formatear las cuentas como eventos para el calendario
            var eventos = cuentas.map(function(cuenta) {
                return {
                    title: cuenta.idcue, // Nombre de la cuenta
                    start: cuenta.fechavencue, // Fecha de vencimiento
                    allDay: true // Mostrar como evento de día completo
                };
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
                events: eventos // Cargar los eventos en el calendario
            });

            calendar.render();
        });
    </script>
@endsection
