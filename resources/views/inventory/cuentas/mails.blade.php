@extends('layouts.navigation')

@section('title', 'Correos')

@section('main')
<div class="container-fluid px-4">
    <!-- Título y breadcrumb -->
    <h1 class="mt-4">Correos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Correos</li>
    </ol>

    <!-- Descripción y alertas -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h3 class="text-primary">Gestión de Buzones</h3>
        <p class="text-muted">Aquí puedes ver los buzones de correo de Streamify, estos pueden ocuparse para crear cuentas.</p>
    </div>

    <!-- Botón crear buzón -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearMailModal">
            <i class="fas fa-plus"></i> Crear Buzón
        </button>
    </div>

    <!-- Tabla Enhanced v2 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Lista de Correos
            </h6>
        </div>
        <div class="card-body">
            <!-- Encabezado: Búsqueda y Registros por página -->
            <div class="row mb-3 align-items-end">
                <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                    <label for="mails-table-search" class="form-label fw-semibold">
                        <i class="fas fa-search text-primary"></i> Buscar:
                    </label>
                    <input id="mails-table-search"
                           type="text"
                           placeholder="Buscar por email, host, descripción..."
                           class="form-control">
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <label for="mails-table-rows-per-page" class="form-label fw-semibold">
                        <i class="fas fa-list text-primary"></i> Mostrar:
                    </label>
                    <select id="mails-table-rows-per-page" class="form-select">
                        <option value="5" selected>5 registros</option>
                        <option value="10">10 registros</option>
                        <option value="20">20 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="mails-table"
                       data-table="mails-table"
                       class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sortable" data-type="number" data-col="0">
                                ID
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="1">
                                Email
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="2">
                                Host
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="3">
                                Descripción
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            <th class="sortable" data-type="string" data-col="4">
                                Creado
                                <span class="sort-arrow">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                    </svg>
                                </span>
                            </th>
                            @if (Auth::user()->hasAnyPermission(['mails.update', 'mails.destroy']))
                                <th data-type="actions">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mails as $mail)
                            <tr>
                                <td>{{ $mail->id }}</td>
                                <td>{{ $mail->email }}</td>
                                <td>{{ $mail->host }}</td>
                                <td>{{ $mail->description }}</td>
                                <td>{{ $mail->created_at->format('Y-m-d') }}</td>
                                @if (Auth::user()->hasAnyPermission(['mails.update', 'mails.destroy']))
                                    <td>
                                        <div class="action-buttons">
                                            @if (Auth::user()->hasPermissionTo('mails.update'))
                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editarMailModal{{ $mail->id }}" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if (Auth::user()->hasPermissionTo('mails.destroy'))
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#eliminarMailModal{{ $mail->id }}" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer: Info y paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div id="mails-table-row-info" class="text-muted"></div>
                </div>
                <div class="col-md-6">
                    <div id="mails-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de Editar y Eliminar -->
    @foreach ($mails as $mail)
        <!-- Modal Editar Mail -->
        <div class="modal fade" id="editarMailModal{{ $mail->id }}" tabindex="-1" aria-labelledby="editarMailModalLabel{{ $mail->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('mails.update', $mail) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editarMailModalLabel{{ $mail->id }}">Editar Buzón</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="email{{ $mail->id }}">Email</label>
                                <input type="email" name="email" id="email{{ $mail->id }}" class="form-control" value="{{ $mail->email }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password{{ $mail->id }}">Contraseña</label>
                                <input type="text" name="password" id="password{{ $mail->id }}" class="form-control" value="{{ $mail->password }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="host{{ $mail->id }}">Host</label>
                                <input type="text" name="host" id="host{{ $mail->id }}" class="form-control" value="{{ $mail->host }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="description{{ $mail->id }}">Descripción</label>
                                <input type="text" name="description" id="description{{ $mail->id }}" class="form-control" value="{{ $mail->description }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Eliminar Mail -->
        <div class="modal fade" id="eliminarMailModal{{ $mail->id }}" tabindex="-1" aria-labelledby="eliminarMailModalLabel{{ $mail->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('mails.destroy', $mail) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="eliminarMailModalLabel{{ $mail->id }}">Eliminar Buzón</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ¿Seguro que deseas eliminar este buzón?
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</div>

<!-- Modal Crear Mail -->
<div class="modal fade" id="crearMailModal" tabindex="-1" aria-labelledby="crearMailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('mails.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearMailModalLabel">Crear Buzón</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="password">Contraseña</label>
                        <input type="text" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="host">Host</label>
                        <input type="text" name="host" id="host" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Descripción</label>
                        <input type="text" name="description" id="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- Enhanced Table v2.0 -->
<script src="{{ asset('js/enhanced-table-v2.js') }}"></script>

<script>
    console.log('Vista de correos cargada con Enhanced Table v2.0');
    console.log('Total de buzones en la tabla:', {{ $mails->count() }});
</script>
@endsection
