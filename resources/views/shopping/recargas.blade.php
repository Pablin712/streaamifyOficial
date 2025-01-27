@extends('layouts.cliente')

@section('title', 'Recargar Saldo')

@section('header')
<div class="container px-5">
    <div class="row gx-5 align-items-center">
        <div class="col-lg-6">
            <h1 class="display-1 lh-1 mb-3">Recarga tu saldo fácilmente</h1>
            <p class="lead fw-normal text-muted mb-5">Selecciona un banco para realizar tu recarga de saldo.
                No pongas descripción en el comprobante de pago.
            </p>
        </div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="text-center">
            <img src="{{ asset('images/saldo.png') }}" alt="Recargar Saldo" class="img-fluid" 
            style="width: 80%; max-width: 600px; height: auto; object-fit: cover;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('sections')
<!-- Mensajes de éxito -->
@if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Mensajes de error -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
<section class="py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">Bancos Disponibles</h2>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4">
            @foreach ($bancos as $banco)
                <div class="col mb-5">
                    <div class="card h-100">
                        <!-- Imagen del banco -->
                        <img class="card-img-top" src="{{ asset($banco->foto) }}" alt="{{ $banco->nombreban }}" />

                        <!-- Detalles del banco -->
                        <div class="card-body text-center">
                            <h5 class="fw-bolder">{{ $banco->nombreban }}</h5>
                            <p class="text-muted">Cuenta: {{ $banco->numeroban }}</p>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer d-flex justify-content-center gap-2">
                            <!-- Botón para abrir modal de información -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#infoModal{{ $banco->idban }}">
                                <i class="bi bi-info-circle"></i> Info
                            </button>

                            <!-- Botón para abrir modal de recarga -->
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#recargarModal{{ $banco->idban }}">
                                <i class="bi bi-wallet2"></i> Recargar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal de Información -->
                <div class="modal fade" id="infoModal{{ $banco->idban }}" tabindex="-1" aria-labelledby="infoModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="infoModalLabel">Información del Banco</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Propietario:</strong> {{ $banco->propietarioban }}</p>
                                <p><strong>Cédula:</strong> {{ $banco->cedulaban }}</p>
                                <p><strong>Tipo:</strong> {{ $banco->tipoban }}</p>
                                <p><strong>Detalles:</strong> {{ $banco->detalleban }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Recarga -->
                <div class="modal fade" id="recargarModal{{ $banco->idban }}" tabindex="-1"
                    aria-labelledby="recargarModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('recargar.saldo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="recargarModalLabel">Recargar Saldo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="idban" value="{{ $banco->idban }}">
                                    {{-- <input type="hidden" name="idcli" value="{{ Auth::user()->idcli }}"> --}}
                                    <div class="mb-3">
                                        <label for="numcomprobante" class="form-label">Número de Comprobante</label>
                                        <input type="text" class="form-control" id="numcomprobante" name="numcomprobante"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="valor" class="form-label">Valor a Recargar</label>
                                        <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="2"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto" class="form-label">Foto del Comprobante</label>
                                        <input type="file" class="form-control" id="foto" name="foto" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Recargar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
