<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title', 'Streamify')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    @yield('styles')
    @livewireStyles
</head>

<body class="sb-nav-fixed">
    @include('partials.navbar')

    <div id="layoutSidenav">
        @include('partials.sidebar')

        <div id="layoutSidenav_content">
            <main>
                @yield('main')
            </main>
            @include('partials.footer')
        </div>
    </div>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/navbar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const dataTableElement = document.querySelector('#datatablesSimple');
                if (dataTableElement) {
                    const rows = dataTableElement.querySelectorAll('tbody tr');
                    if (rows.length > 0) {
                        new simpleDatatables.DataTable(dataTableElement, {
                            searchable: true,
                            perPageSelect: [5, 10, 20],
                            labels: {
                                placeholder: "Buscar...",
                                perPage: "Registros por página",
                                noRows: "No se encontraron registros.",
                                info: "Mostrando {start} a {end} de {rows} registros",
                            },
                        });
                    } else {
                        console.warn('La tabla sigue sin filas después del tiempo de espera.');
                    }
                }
            }, 500);
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    @yield('scripts')
    <script>
        $(document).ready(function() {
            $("#marcarLeidas").click(function() {
                $.ajax({
                    url: "{{ route('notificaciones.leer') }}",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#contadorNotificaciones").remove(); // Ocultar contador
                        }
                    }
                });
            });
        });
        $(document).ready(function() {
            $('.idcue').select2({
                placeholder: "Selecciona una cuenta",
                allowClear: true // Permite borrar la selección
            });
        });
    </script>
    <script>
        let timer;
        let warningTimer;

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;

        function logout() {
            // Envía el formulario de cierre de sesión
            document.getElementById('logoutForm').submit();
        }

        function showWarning() {
            alert("Tu sesión se cerrará automáticamente en 15 segundos debido a inactividad.");
        }

        function resetTimer() {
            clearTimeout(timer);
            clearTimeout(warningTimer);

            // Mostrar advertencia 1 minuto antes del cierre
            warningTimer = setTimeout(showWarning, 10 * 60 * 1000); // 29 minutos de inactividad
            timer = setTimeout(logout, 10.25 * 60 * 1000); // 30 minutos de inactividad
        }
    </script>
</body>

</html>
