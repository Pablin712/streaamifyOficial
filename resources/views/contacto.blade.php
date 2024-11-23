@extends('layouts.layout') 
@section('title') 
Contacto 
@endsection 
@section('content') 
<h1>Contacto</h1> 
<form method="POST" action="{{ route('contacto') }}"> 
@csrf 
<input type="text" name="nombre" placeholder="Nombre...."><br> 
<input type="email" name="correo" placeholder="Correo..."><br> 
<input type="text" name="asunto" placeholder="Asunto..."><br> 
<textarea name="contenido" cols="30" rows="10" ></textarea><br> 
<button>Enviar</button> 
</form> 
@endsection