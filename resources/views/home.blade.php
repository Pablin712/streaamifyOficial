@extends('layouts.navigation') 
@section('title') 
Home 
@endsection 
@section('content') 
<h1>Home</h1> 
Bienvenido {{ $nombre ?? "Invitado" }} 
@endsection