<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('images/icono2.ico') }}" type="image/x-icon">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.html">Admin Pablin</a>
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
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="{{ route('inicio') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Inicio
                        </a>
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Business</div>
                        <!-- Finance collapsible -->
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseFinance" aria-expanded="false" aria-controls="collapseFinance">
                            <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                            Finance
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseFinance" aria-labelledby="headingFinance"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{route('costos')}}">Costs</a>
                                <a class="nav-link" href="{{route('gastos')}}">Expenses</a>
                                <a class="nav-link" href="reports.html">Reports</a>
                            </nav>
                        </div> {{-- finance collapsible end --}}
                        {{-- Aquí empieza el colapsable Sales --}}
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Sales
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="layout-static.html">Sales details</a>
                                <a class="nav-link" href="{{route('clientes')}}">Customers</a>
                            </nav>
                        </div>
                        {{-- Aquí termina el colapsable Sales --}}

                        <div class="sb-sidenav-menu-heading">Inventory</div>
                        <!-- Accounts collapsible -->
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseAccounts" aria-expanded="false"
                            aria-controls="collapseAccounts">
                            <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                            Accounts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseAccounts" aria-labelledby="headingAccounts"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{route('cuentas')}}">Accounts and profiles</a>
                                <a class="nav-link" href="settings.html">Active Users</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapseStock" aria-expanded="false" aria-controls="collapseStock">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Stock
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseStock" aria-labelledby="headingStock"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{route('servicios')}}">Services</a>
                                <a class="nav-link" href="{{route('proveedores')}}">Proveedores</a>
                                <a class="nav-link" href="{{route('valores')}}">Values</a>
                            </nav>
                        </div>

                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Pablin
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
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/demo/chart-bar-demo.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Espera a que los datos estén disponibles (ajusta el tiempo si es necesario)
        setTimeout(function () {
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
@yield('scripts')
</body>

</html>
