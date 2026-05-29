{{-- Tabla de Resultados del Mes --}}
<div id="resultados" class="card mb-4">
    <div class="card-header" style="background-color: var(--bg-light);">
        <h5 class="mb-0" style="color: var(--text-primary);">
            <i class="fas fa-table me-2"></i>Resultados del Mes - Análisis por Servicio
        </h5>
    </div>
    <div class="card-body">
        <!-- Controles de búsqueda y registros -->
        <div class="row mb-3 align-items-end">
            <div class="col-lg-8 col-md-7 col-12 mb-3 mb-md-0">
                <label for="resultados-table-search" class="form-label fw-semibold">
                    <i class="fas fa-search text-primary"></i> Buscar:
                </label>
                <input id="resultados-table-search"
                       type="text"
                       placeholder="Buscar servicio..."
                       class="form-control">
            </div>
            <div class="col-lg-4 col-md-5 col-12">
                <label for="resultados-table-rows-per-page" class="form-label fw-semibold">
                    <i class="fas fa-list text-primary"></i> Mostrar:
                </label>
                <select id="resultados-table-rows-per-page" class="form-select">
                    <option value="5">5 registros</option>
                    <option value="10" selected>10 registros</option>
                    <option value="20">20 registros</option>
                    <option value="50">50 registros</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="resultados-table" data-table="resultados-table" data-default-rows="10" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="sortable" data-type="string" data-col="0">
                            Servicio
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="1">
                            Cta
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="2">
                            Usuarios
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="3">
                            Usu/Cue
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="4">
                            Ingresos
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="5">
                            Costos
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="6">
                            Ganancias
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="7">
                            Renta
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="8">
                            Ing/Cli
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="9">
                            Cos/Cli
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                        <th class="sortable" data-type="number" data-col="10">
                            Gan/Cli
                            <span class="sort-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 11l5-5 5 5M7 13l5 5 5-5"/>
                                </svg>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $servicios = [
                            ['nombre' => 'Netflix', 'cuentas' => $cuentas_netflix, 'usuarios' => $usuarios_netflix, 'ingresos' => $ingresos_netflix, 'costos' => $costos_netflix],
                            ['nombre' => 'Disney', 'cuentas' => $cuentas_disney, 'usuarios' => $usuarios_disney, 'ingresos' => $ingresos_disney, 'costos' => $costos_disney],
                            ['nombre' => 'Prime', 'cuentas' => $cuentas_prime, 'usuarios' => $usuarios_prime, 'ingresos' => $ingresos_prime, 'costos' => $costos_prime],
                            ['nombre' => 'Max', 'cuentas' => $cuentas_max, 'usuarios' => $usuarios_max, 'ingresos' => $ingresos_max, 'costos' => $costos_max],
                            ['nombre' => 'Magis', 'cuentas' => $cuentas_magis, 'usuarios' => $usuarios_magis, 'ingresos' => $ingresos_magis, 'costos' => $costos_magis],
                            ['nombre' => 'Crunchy', 'cuentas' => $cuentas_crunchy, 'usuarios' => $usuarios_crunchy, 'ingresos' => $ingresos_crunchy, 'costos' => $costos_crunchy],
                            ['nombre' => 'Paramount', 'cuentas' => $cuentas_paramount, 'usuarios' => $usuarios_paramount, 'ingresos' => $ingresos_paramount, 'costos' => $costos_paramount],
                            ['nombre' => 'Spotify', 'cuentas' => $cuentas_spotify, 'usuarios' => $usuarios_spotify, 'ingresos' => $ingresos_spotify, 'costos' => $costos_spotify],
                            ['nombre' => 'Otros', 'cuentas' => $cuentas_otros, 'usuarios' => $usuarios_otros, 'ingresos' => $ingresos_otros, 'costos' => $costos_otros],
                        ];
                    @endphp

                    @foreach ($servicios as $servicio)
                        <tr>
                            <td><strong>{{ $servicio['nombre'] }}</strong></td>
                            <td>{{ $servicio['cuentas'] }}</td>
                            <td>{{ $servicio['usuarios'] }}</td>
                            <td>
                                @if ($servicio['cuentas'] != 0)
                                    {{ number_format($servicio['usuarios'] / $servicio['cuentas'], 2) }}
                                @else
                                    0
                                @endif
                            </td>
                            <td>${{ number_format($servicio['ingresos'], 2) }}</td>
                            <td>${{ number_format($servicio['costos'], 2) }}</td>
                            <td class="{{ ($servicio['ingresos'] - $servicio['costos']) >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>${{ number_format($servicio['ingresos'] - $servicio['costos'], 2) }}</strong>
                            </td>
                            <td>
                                @if ($servicio['costos'] != 0)
                                    {{ number_format($servicio['ingresos'] / $servicio['costos'], 2) }}
                                @else
                                    0
                                @endif
                            </td>
                            <td>
                                @if ($servicio['usuarios'] != 0)
                                    ${{ number_format($servicio['ingresos'] / $servicio['usuarios'], 2) }}
                                @else
                                    $0
                                @endif
                            </td>
                            <td>
                                @if ($servicio['usuarios'] != 0)
                                    ${{ number_format($servicio['costos'] / $servicio['usuarios'], 2) }}
                                @else
                                    $0
                                @endif
                            </td>
                            <td>
                                @if ($servicio['usuarios'] != 0)
                                    ${{ number_format(($servicio['ingresos'] - $servicio['costos']) / $servicio['usuarios'], 2) }}
                                @else
                                    $0
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- Fila de Totales --}}
                    <tr class="table-primary fw-bold">
                        <td><strong>TOTALES</strong></td>
                        <td><strong>{{ $num_cuentas }}</strong></td>
                        <td><strong>{{ $total_usuarios_activos }}</strong></td>
                        <td>
                            @if ($num_cuentas != 0)
                                <strong>{{ number_format($total_usuarios_activos / $num_cuentas, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td><strong>${{ number_format($ingresos_mes, 2) }}</strong></td>
                        <td><strong>${{ number_format($costos_mes, 2) }}</strong></td>
                        <td class="{{ ($ingresos_mes - $costos_mes) >= 0 ? 'text-success' : 'text-danger' }}">
                            <strong>${{ number_format($ingresos_mes - $costos_mes, 2) }}</strong>
                        </td>
                        <td>
                            @if ($costos_mes != 0)
                                <strong>{{ number_format($ingresos_mes / $costos_mes, 2) }}</strong>
                            @else
                                <strong>0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>${{ number_format($ingresos_mes / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>$0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>${{ number_format($costos_mes / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>$0</strong>
                            @endif
                        </td>
                        <td>
                            @if ($total_usuarios_activos != 0)
                                <strong>${{ number_format(($ingresos_mes - $costos_mes) / $total_usuarios_activos, 2) }}</strong>
                            @else
                                <strong>$0</strong>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Información de paginación y controles -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6 col-12 mb-2 mb-md-0">
                <div id="resultados-table-row-info" class="text-muted"></div>
            </div>
            <div class="col-md-6 col-12">
                <div id="resultados-table-pagination" class="d-flex justify-content-end flex-wrap"></div>
            </div>
        </div>
    </div>
</div>
