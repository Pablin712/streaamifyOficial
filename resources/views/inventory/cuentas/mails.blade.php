@extends('layouts.table')

@section('title', 'Correos')
@section('h1', 'Correos')
@section('breadcrumb')
    Correos
@endsection
@section('descripcion')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <p>Aquí puedes ver los buzones de correo de Streamify, estos pueden ocuparse para crear cuentas.</p>
@endsection

@section('btncrear')
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearMailModal">
        Crear Buzón
    </button>
@endsection

@section('tablename', 'Correos')
@section('table1')
    <table id="datatablesSimple" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Host</th>
                <th>Descripción</th>
                <th>Creado</th>
                @if (Auth::user()->hasAnyPermission(['mails.update', 'mails.destroy']))
                    <th>Acciones</th>
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
                            @if (Auth::user()->hasPermissionTo('mails.update'))
                                <!-- Editar mail -->
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editarMailModal{{ $mail->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                            @if (Auth::user()->hasPermissionTo('mails.destroy'))
                                <!-- Eliminar mail -->
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#eliminarMailModal{{ $mail->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>

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
        </tbody>
    </table>
@endsection

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
