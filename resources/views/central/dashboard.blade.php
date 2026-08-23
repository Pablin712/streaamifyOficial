<!doctype html>
<html lang="es" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Central — Streamify SaaS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand navbar-dark bg-primary mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">Streamify SaaS — Panel Central</span>
            <form action="{{ route('central.logout') }}" method="POST" class="ms-auto">
                @csrf
                <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </nav>

    <div class="container">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header fw-bold">Crear Tenant nuevo</div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('central.tenants.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nombre del negocio</label>
                                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subdominio</label>
                                <div class="input-group">
                                    <input type="text" name="subdominio" class="form-control" value="{{ old('subdominio') }}" required>
                                    <span class="input-group-text">.{{ $baseDomain }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Crear Tenant</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header fw-bold">Tenants ({{ $tenants->count() }})</div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Dominio</th>
                                    <th>Id</th>
                                    <th>Creado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->nombre ?? '—' }}</td>
                                        <td>
                                            @forelse ($tenant->domains as $domain)
                                                <code>{{ $domain->domain }}.{{ $baseDomain }}</code>
                                            @empty
                                                <span class="text-muted">sin dominio</span>
                                            @endforelse
                                        </td>
                                        <td><code class="text-muted">{{ $tenant->id }}</code></td>
                                        <td>{{ $tenant->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Todavia no hay tenants.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
