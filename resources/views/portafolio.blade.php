@extends('layouts.layout') 
@section('title') 
Portafolio 
@endsection 
@section('content') 
    <h1>Portafolio</h1> 
    <ul> 
        @isset($proyectos) 
            @foreach($proyectos as $proyecto) 
                <li>{{$proyecto['titulo']}}</li> 
            @endforeach 
        @else 
            <li>No hay proyectos</li> 
        @endisset 
    </ul> 
@endsection