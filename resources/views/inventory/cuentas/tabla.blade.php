@php
    // Generar ID único para la tabla basado en el contexto o usar uno por defecto
    $tableId = $tableId ?? 'cuentas-table-' . uniqid();
@endphp

<!-- Encabezado: Búsqueda y Registros por página -->
<div class="row mb-3 align-items-end">
    <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
        <label for="{{ $tableId }}-search" class="form-label fw-semibold">
            <i class="fas fa-search text-primary"></i> Buscar:
        </label>
        <input id="{{ $tableId }}-search"
               type="text"
               placeholder="Buscar por ID, servicio, usuario, estado..."
               class="form-control">
    </div>
    <div class="col-lg-4 col-md-5 col-12">
        <label for="{{ $tableId }}-rows-per-page" class="form-label fw-semibold">
            <i class="fas fa-list text-primary"></i> Mostrar:
        </label>
        <select id="{{ $tableId }}-rows-per-page" class="form-select">
            <option value="5" selected>5 registros</option>
            <option value="10">10 registros</option>
            <option value="20">20 registros</option>
            <option value="50">50 registros</option>
            <option value="100">100 registros</option>
        </select>
    </div>
</div>

<div class="table-responsive">
    <table id="{{ $tableId }}"
           data-table="{{ $tableId }}"
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
                    Servicio
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="2">
                    Usuario
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="3">
                    Clave
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="4">
                    Vence
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="5">
                    Clientes
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="6">
                    Estado
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasAnyPermission(['cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                    <th data-type="actions">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($cuentas as $cuenta)
                @php
                    // Convertir la fecha de vencimiento a Carbon
                    $fechaVencimiento = \Carbon\Carbon::parse($cuenta->fechavencue);
                    $hoy = \Carbon\Carbon::today();
                    $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
                @endphp
                <tr>
                    <td>{{ $cuenta->idcue }}</td>
                    <td>{{ $cuenta->valor->idser }}-{{ $cuenta->valor->proveedor->nombrepro }}</td>
                    <td>{{ $cuenta->usuariocue }}</td>
                    <td>{{ $cuenta->contrasenacue }}</td>
                    <td>{{ $cuenta->fechavencue }}</td>
                    <td>
                        @php
                            $users = $cuenta->usuarios_activos;
                        @endphp
                        @if ($cuenta->valor->pantmaxval < $users)
                            <span class="badge bg-dark">{{ $users }}</span>
                        @elseif ($cuenta->valor->pantminval > $users)
                            <span class="badge bg-danger">{{ $users }}</span>
                        @else
                            <span class="badge bg-success">{{ $users }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($cuenta->caidacue)
                            <span class="badge bg-dark">Dañada</span>
                        @elseif ($diasRestantes <= 0)
                            <span class="badge bg-danger">Vencida</span>
                        @elseif ($diasRestantes <= 5)
                            <span class="badge bg-warning">Ya vence</span>
                        @else
                            <span class="badge bg-success">Activa</span>
                        @endif
                        @if (Auth::user()->hasPermissionTo('cuentas.status'))
                            <!-- Botón para cambiar estado -->
                            <form action="{{ route('cuentas.status', $cuenta->idcue) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-dark btn-sm">
                                    @if ($cuenta->caidacue)
                                        <i class="fas fa-toggle-on fa-xs"></i>
                                    @else
                                        <i class="fas fa-toggle-off fa-xs"></i>
                                    @endif
                                </button>
                            </form>
                        @endif
                    </td>
                    @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                        <td>
                            <div class="action-buttons">
                                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                    <a href="{{ route('cuentas.show', $cuenta->idcue) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.edit'))
                                    <a href="{{ route('cuentas.edit', $cuenta->idcue) }}" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                                @if ($diasRestantes <= 5 || $diasRestantes < 0)
                                    @if (Auth::user()->hasPermissionTo('cuentas.renew'))
                                        <a href="{{ route('cuentas.renew', $cuenta->idcue) }}"
                                            class="btn btn-success btn-xs">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    @endif
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
                                    <form action="{{ route('cuentas.destroy', $cuenta->idcue) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('¿Estás seguro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay cuentas disponibles en esta categoría.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Footer: Info y paginación -->
<div class="row mt-3">
    <div class="col-md-6">
        <div id="{{ $tableId }}-row-info" class="text-muted"></div>
    </div>
    <div class="col-md-6">
        <div id="{{ $tableId }}-pagination" class="d-flex justify-content-end flex-wrap"></div>
    </div>
</div>
