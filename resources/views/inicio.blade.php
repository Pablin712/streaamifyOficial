@extends('layouts.static') 
@section('title')
Inicio
@endsection
@section('h1','Inicio')
@section('introduccion') 
<h1>Bienvenido user (employee)</h1>
<p>pantalla principal al que se accede</p>
<h2>What do you do today?</h2>
<h3>¿Qué harás hoy?</h3>
@endsection
@section('content')
<h3>Mapa de erp</h3>
<img src="{{asset('images/BASE2.png')}}" alt="imagen de mapa">
@endsection
@section('pie')
    Realiza las tareas
@endsection