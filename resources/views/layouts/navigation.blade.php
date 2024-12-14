<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/Icono.png') }}" type="image/x-icon">
    <!-- Agregar el CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @yield('styles')
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="{{ route('inicio')}}">Streamify HQ</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i
                class="fas fa-bars"></i></button>
        <!-- Navbar SSearch-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..."
                    aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i
                        class="fas fa-search"></i></button>
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i> {{ Auth::user()->nombreemp }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Ajustes</a></li>
                        <li><a class="dropdown-item" href="#!">Actividad</a></li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </li>
            @endauth
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Principal</div>
                        <a class="nav-link" href="{{ route('inicio') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Inicio
                        </a>
                        @if (Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'contador')
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Dashboard
                        </a>
                        @endif
                        <div class="sb-sidenav-menu-heading">Negocio</div>
                        <!-- Finance collapsible -->
                        @if(Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'contador'
                        || Auth::user()->idrol == 'bodeguero')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseFinance" aria-expanded="false" aria-controls="collapseFinance">
                            <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                            Finanza
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseFinance" aria-labelledby="headingFinance"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                
                                <a class="nav-link" href="{{ route('costos') }}">Costos</a>
                                @if(Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'contador')
                                <a class="nav-link" href="{{ route('gastos') }}">Gastos</a>
                                @endif
                                <!--<a class="nav-link" href="reports.html">Reports</a>-->
                            </nav>
                        </div>
                        @endif {{-- finance collapsible end --}}
                        {{-- Aquí empieza el colapsable Sales --}}
                        @if(Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'vendedor'
                        || Auth::user()->idrol == 'tecnico')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Comercio
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{ route('ventas') }}">Ventas</a>
                                <a class="nav-link" href="{{ route('clientes') }}">Clientes</a>
                            </nav>
                        </div>
                        @endif
                        {{-- Aquí termina el colapsable Sales --}}
                        @if(Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'vendedor'
                        || Auth::user()->idrol == 'tecnico' || Auth::user()->idrol == 'bodeguero')
                        <div class="sb-sidenav-menu-heading">Stock</div>
                        <!-- Accounts collapsible -->
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseAccounts" aria-expanded="false"
                            aria-controls="collapseAccounts">
                            <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                            Cuentas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseAccounts" aria-labelledby="headingAccounts"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @if(Auth::user()->idrol != 'vendedor')
                                <a class="nav-link" href="{{ route('cuentas') }}">Cuentas y Perfiles</a>
                                @endif
                                <a class="nav-link" href="{{ route('usuarios') }}">Usuarios Activos</a>
                            </nav>
                        </div>
                        @endif

                        @if(Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'bodeguero'
                        || Auth::user()->idrol == 'contador' || Auth::user()->idrol == 'vendedor')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseStock" aria-expanded="false" aria-controls="collapseStock">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Inventario
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseStock" aria-labelledby="headingStock"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{ route('servicios') }}">Servicios</a>
                                @if (Auth::user()->idrol == 'administrador' || Auth::user()->idrol == 'bodeguero')
                                <a class="nav-link" href="{{ route('proveedores') }}">Proveedores</a>
                                @endif
                                <a class="nav-link" href="{{ route('valores') }}">Valores</a>
                            </nav>
                        </div>
                        @endif
                    
                        @if(Auth::user()->idrol == 'administrador')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseAdministration" aria-expanded="false" aria-controls="collapseAdministration">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Administracion
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseAdministration" aria-labelledby="headingAdministration"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{ route('empleados') }}">Empleados</a>
                            </nav>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Iniciado como:</div>
                    {{ Auth::user()->idrol ?? 'Guest' }}
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                @yield('main')
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Streamify 2024</div>
                        <div>
                            <a href="#">Politicas de Privacidad</a>
                            &middot;
                            <a href="#">Terminos &amp; Condiciones</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <!-- <script src="{{ asset('assets/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/demo/chart-bar-demo.js') }}"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Espera a que los datos estén disponibles (ajusta el tiempo si es necesario)
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
                                perPage: "{select} registros por página",
                                noRows: "No se encontraron registros.",
                                info: "Mostrando {start} a {end} de {rows} registros",
                            },
                        });
                    } else {
                        console.warn('La tabla sigue sin filas después del tiempo de espera.');
                    }
                }
            }, 500); // Ajusta el tiempo de espera si es necesario
        });
    </script>
    <!-- Agregar el JavaScript de Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @yield('scripts')
</body>

</html>
