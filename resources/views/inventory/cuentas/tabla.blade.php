@php
    // Generar ID único para la tabla basado en el contexto o usar uno por defecto
    $tableId = $tableId ?? 'cuentas-table-' . uniqid();
    $emptyColspan = 8;
    if (Auth::user()->hasPermissionTo('cuentas.mensaje')) {
        $emptyColspan++;
    }
    if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'cuentas.edit', 'cuentas.renew', 'cuentas.destroy'])) {
        $emptyColspan++;
    }
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
                <th class="sortable" data-type="string" data-col="5">
                    Última actualización
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="number" data-col="6">
                    Clientes
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                <th class="sortable" data-type="string" data-col="7">
                    Estado
                    <span class="sort-arrow">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                        </svg>
                    </span>
                </th>
                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                    <th class="text-center" data-type="actions" style="width: 70px;">
                        <input
                            type="checkbox"
                            class="form-check-input provider-inventory-select-all"
                            data-table-id="{{ $tableId }}"
                            title="Seleccionar todas las cuentas visibles de esta tabla"
                        >
                    </th>
                @endif
                @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
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
                <tr
                    data-account-row="1"
                    data-service-code="{{ strtoupper((string) ($cuenta->valor->idser ?? '')) }}"
                    data-idcue="{{ $cuenta->idcue }}"
                    data-usuario="{{ $cuenta->usuariocue }}"
                    data-fechavencue="{{ $cuenta->fechavencue }}"
                    data-servicio-id="{{ strtoupper((string) ($cuenta->valor->idser ?? '')) }}"
                    data-servicio-nombre="{{ $cuenta->valor->servicio->nombreser ?? ($cuenta->valor->idser ?? 'Servicio') }}"
                    data-proveedor-id="{{ $cuenta->valor->proveedor->idpro ?? '' }}"
                    data-proveedor-nombre="{{ $cuenta->valor->proveedor->nombrepro ?? 'Proveedor' }}"
                    data-proveedor-telefono="{{ $cuenta->valor->proveedor->telefonopro ?? '' }}"
                >
                    <td class="clickable-copy"
                        onclick="copiarInfoCuenta('{{ $cuenta->valor->servicio->nombreser ?? 'Servicio' }}', '{{ $cuenta->usuariocue }}', '{{ $cuenta->contrasenacue }}')"
                        title="Click para copiar información completa">
                        {{ $cuenta->idcue }}
                    </td>
                    <td>{{ $cuenta->valor->idser }}-{{ $cuenta->valor->proveedor->nombrepro }}</td>
                    <td class="clickable-copy"
                        onclick="copiarTexto('{{ $cuenta->usuariocue }}', 'Usuario')"
                        title="Click para copiar usuario">
                        {{ $cuenta->usuariocue }}
                    </td>
                    <td class="clickable-copy"
                        onclick="copiarTexto('{{ $cuenta->contrasenacue }}', 'Contraseña')"
                        title="Click para copiar contraseña">
                        {{ $cuenta->contrasenacue }}
                    </td>
                    <td>{{ $cuenta->fechavencue }}</td>
                    <td>{{ optional($cuenta->updated_at)->format('d/m/Y H:i') ?? 'N/A' }}</td>
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
                            <span class="badge bg-dark status-badge" data-idcue="{{ $cuenta->idcue }}">Dañada</span>
                        @elseif ($diasRestantes <= 0)
                            <span class="badge bg-danger status-badge" data-idcue="{{ $cuenta->idcue }}">Vencida</span>
                        @elseif ($diasRestantes <= 5)
                            <span class="badge bg-warning status-badge" data-idcue="{{ $cuenta->idcue }}">Ya vence</span>
                        @else
                            <span class="badge bg-success status-badge" data-idcue="{{ $cuenta->idcue }}">Activa</span>
                        @endif
                        @if (Auth::user()->hasPermissionTo('cuentas.status'))
                            <!-- Botón para cambiar estado en tiempo real -->
                            <button type="button"
                                class="btn {{ $cuenta->caidacue ? 'btn-danger' : 'btn-success' }} btn-sm ms-1"
                                onclick="event.preventDefault(); toggleEstado('{{ $cuenta->idcue }}', {{ $cuenta->caidacue ? 'true' : 'false' }})"
                                title="Cambiar estado de la cuenta"
                                data-idcue="{{ $cuenta->idcue }}">
                                @if ($cuenta->caidacue)
                                    <i class="fas fa-toggle-on fa-xs"></i>
                                @else
                                    <i class="fas fa-toggle-off fa-xs"></i>
                                @endif
                            </button>
                        @endif
                    </td>
                    @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                        <td class="text-center align-middle">
                            <input
                                type="checkbox"
                                class="form-check-input provider-inventory-checkbox"
                                data-table-id="{{ $tableId }}"
                                data-idcue="{{ $cuenta->idcue }}"
                                data-usuario="{{ $cuenta->usuariocue }}"
                                data-fechavencue="{{ $cuenta->fechavencue }}"
                                data-servicio-id="{{ strtoupper((string) ($cuenta->valor->idser ?? '')) }}"
                                data-servicio-nombre="{{ $cuenta->valor->servicio->nombreser ?? ($cuenta->valor->idser ?? 'Servicio') }}"
                                data-proveedor-id="{{ $cuenta->valor->proveedor->idpro ?? '' }}"
                                data-proveedor-nombre="{{ $cuenta->valor->proveedor->nombrepro ?? 'Proveedor' }}"
                                data-proveedor-telefono="{{ $cuenta->valor->proveedor->telefonopro ?? '' }}"
                                title="Seleccionar cuenta para envío de inventario"
                                          data-usuarios-activos="{{ $cuenta->usuarios_activos }}"
                                     >
                        </td>
                    @endif
                    @if (Auth::user()->hasAnyPermission(['cuentas.mensaje', 'cuentas.edit', 'cuentas.renew', 'cuentas.destroy']))
                        <td>
                            <div class="action-buttons">
                                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                    @php
                                        $cooldownUntil = (int) \Illuminate\Support\Facades\Cache::get('cuenta_msg_cooldown_' . $cuenta->idcue, 0);
                                        $isCooldownActive = $cooldownUntil > now()->timestamp;
                                        $telefonoProveedor = $cuenta->valor->proveedor->telefonopro ?? null;
                                        $canRequestNetflixCode = strtoupper((string) ($cuenta->valor->idser ?? '')) === strtoupper((string) config('services.netflix_code.service_id', 'NETFLIX'));
                                    @endphp
                                    <button
                                        type="button"
                                        class="btn btn-success btn-xs"
                                        title="Enviar mensaje personalizado a clientes"
                                        data-idcue="{{ $cuenta->idcue }}"
                                        data-servicio="{{ $cuenta->valor->idser ?? 'N/A' }}"
                                        data-cuenta-usuario="{{ $cuenta->usuariocue }}"
                                        data-clientes-activos="{{ $cuenta->usuarios_activos }}"
                                        data-cooldown-until="{{ $cooldownUntil }}"
                                        onclick="openMensajeClientesModal(this)"
                                        @if ($isCooldownActive) disabled @endif
                                    >
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-success btn-xs"
                                        title="Enviar mensaje al proveedor"
                                        data-idcue="{{ $cuenta->idcue }}"
                                        data-servicio="{{ $cuenta->valor->servicio->nombreser ?? ($cuenta->valor->idser ?? 'Servicio') }}"
                                        data-cuenta-usuario="{{ $cuenta->usuariocue }}"
                                        data-cuenta-clave="{{ $cuenta->contrasenacue }}"
                                        data-proveedor="{{ $cuenta->valor->proveedor->nombrepro ?? 'Proveedor' }}"
                                        data-proveedor-id="{{ $cuenta->valor->proveedor->idpro ?? '' }}"
                                        data-proveedor-telefono="{{ $telefonoProveedor }}"
                                        onclick="openMensajeProveedorModal(this)"
                                        @if (empty($telefonoProveedor)) disabled @endif
                                    >
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    @if ($canRequestNetflixCode)
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-xs"
                                            title="Pedir codigo de Netflix"
                                            data-idcue="{{ $cuenta->idcue }}"
                                            data-cuenta-usuario="{{ $cuenta->usuariocue }}"
                                            data-proveedor="{{ $cuenta->valor->proveedor->nombrepro ?? 'Proveedor' }}"
                                            onclick="openNetflixCodigoModal(this)"
                                        >
                                            <i class="fas fa-key"></i>
                                        </button>
                                    @endif
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.mensaje'))
                                    <button onclick="loadPerfilesInModal('{{ $cuenta->idcue }}', '{{ $cuenta->usuariocue }}')" class="btn btn-info btn-xs" title="Ver perfiles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.edit'))
                                    <button onclick="openEditModal('{{ $cuenta->idcue }}')" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                <!-- Botón de renovación: Solo visible si la cuenta está por vencer o vencida -->
                                @if ($diasRestantes <= 5 || $diasRestantes < 0)
                                    @if (Auth::user()->hasPermissionTo('cuentas.renew'))
                                        <button onclick="openRenewModal('{{ $cuenta->idcue }}')" class="btn btn-success btn-xs">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    @endif
                                @endif
                                @if (Auth::user()->hasPermissionTo('cuentas.destroy'))
                                    <button onclick="openDeleteModal('{{ $cuenta->idcue }}')" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr data-empty-row="1">
                    <td colspan="{{ $emptyColspan }}" class="text-center">No hay cuentas disponibles en esta categoría.</td>
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
