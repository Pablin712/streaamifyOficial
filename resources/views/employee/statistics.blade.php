@extends('layouts.static')

@section('h1', 'Actividad de Empleados')
@section('breadcrumb')
    Actividad de Empleados
@endsection
@section('introduccion')
    Aquí puedes ver la actividad de los empleados en el sistema.
@endsection
@section('content')
    @foreach ($datos as $info)
        <div class="mb-8 bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold text-carpintero-primary mb-2">
                {{ $info['empleado']->nombreemp }}
            </h2>
            <p class="text-carpintero-black mb-2">
                Total conectado hoy: <strong>{{ $info['total'] }} minutos</strong>
            </p>

            <table class="w-full table-auto border mt-3 text-sm">
                <thead>
                    <tr class="bg-carpintero-light text-carpintero-black">
                        <th class="px-3 py-2 border">Inicio</th>
                        <th class="px-3 py-2 border">Fin</th>
                        <th class="px-3 py-2 border">Duración (min)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($info['lapsos'] as $lapso)
                        <tr>
                            <td class="px-3 py-2 border">{{ $lapso['inicio']->format('H:i') }}</td>
                            <td class="px-3 py-2 border">{{ $lapso['fin']->format('H:i') }}</td>
                            <td class="px-3 py-2 border">{{ $lapso['tiempo_conexion'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-3">Sin actividad registrada hoy</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
@endsection
