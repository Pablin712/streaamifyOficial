@extends('layouts.static')
@section('title', 'Tareas')
@section('breadcrumb')
    Tareas
@endsection
@section('introduccion')
    <p>Realiza todas las tareas esenciales del negocio en tu jornada laboral. Cuando termines tu trabajo,
        antes de irte agrega tareas para que la persona de turno pueda completar lo que falta, o lo siguiente
        por hacer. Gracias por tu colaboración.
    </p>
@endsection
@section('content')
    <div class="container">
        <h1 class="text-center my-4">Lista de Tareas</h1>

        <!-- Botón para abrir el modal -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#agregarTareaModal">
            <i class="fas fa-plus"></i> Agregar Tarea
        </button>

        <!-- Modal de Agregar Tarea -->
        <div class="modal fade" id="agregarTareaModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('tareas.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Tarea</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label>Nombre</label>
                            <input type="text" name="nombretarea" class="form-control" required>
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control"></textarea>
                            <label>Prioridad</label>
                            <select name="prioridad" class="form-control">
                                <option value="alta">Alta</option>
                                <option value="media">Media</option>
                                <option value="baja">Baja</option>
                            </select>
                            <label>Fecha Límite</label>
                            <input type="datetime-local" name="fechalimit" class="form-control">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de tareas sin completar -->
        <div class="list-group mb-4">
            <h4>Tareas Pendientes</h4>
            @foreach ($tareas as $tarea)
                @if (!$tarea->completada)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $tarea->prioridad === 'alta' ? 'danger' : ($tarea->prioridad === 'media' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($tarea->prioridad) }}
                            </span>
                            {{ $tarea->nombretarea }}
                            <small class="text-muted d-block">{{ $tarea->descripcion }}</small>
                        </div>
                        <div>
                            <!-- Botón completar -->
                            <form action="{{ route('tareas.completar', $tarea->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <!-- Botón eliminar -->
                            @if (Auth::user()->hasPermissionTo('tareas.destroy'))
                                <form action="{{ route('tareas.destroy', $tarea->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Desplegable para tareas completadas -->
        <div class="mb-4">
            <h4>
                <a class="text-decoration-none" data-bs-toggle="collapse" href="#completedTasks" role="button" aria-expanded="false" aria-controls="completedTasks">
                    Ver Tareas Completadas
                </a>
            </h4>
            <div class="collapse" id="completedTasks">
                <div class="list-group">
                    @foreach ($tareas as $tarea)
                        @if ($tarea->completada)
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-light text-decoration-line-through">
                                <div>
                                    <span class="badge bg-{{ $tarea->prioridad === 'alta' ? 'danger' : ($tarea->prioridad === 'media' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($tarea->prioridad) }}
                                    </span>
                                    {{ $tarea->nombretarea }}
                                    <small class="text-muted d-block">{{ $tarea->descripcion }}</small>
                                </div>
                                <div>
                                    <!-- Botón eliminar -->
                                    @if (Auth::user()->hasPermissionTo('tareas.destroy'))
                                        <form action="{{ route('tareas.destroy', $tarea->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection