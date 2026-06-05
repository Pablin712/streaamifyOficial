@extends('layouts.static')
@section('title', 'Tareas')
@section('breadcrumb') Tareas @endsection
@section('introduccion')
    <p>
        Toma las tareas del pool que quieras trabajar, o espera a que te asignen.
        Cuando termines, márcalas como completadas. Puedes devolver una tarea al pool
        si no puedes hacerla.
    </p>
@endsection
@section('content')
    <div class="container-fluid px-2 px-md-3">
        @livewire('tareas-board')
    </div>
@endsection
