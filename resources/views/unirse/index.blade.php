@extends('layouts.app')

@section('ancho', 'angosto')
@section('titulo', 'Unirse a un grupo · Cuentas Claras')

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.index')])
    <div class="title">Unirse a un grupo</div>
    <div class="espacio"></div>
</div>
@endsection

@section('content')
{{-- Aquí va la pestaña "Escanear QR" (lector con la cámara) --}}
<form class="panel on" method="POST" action="{{ route('unirse.buscar') }}">
    @csrf
    <div class="intro">
        <h1>Ingresa el código</h1>
        <p>Pídeselo a alguien del grupo. Son 6 caracteres, como <b>7K4-M2Q</b>.</p>
    </div>
    <input class="code-input" name="codigo" value="{{ old('codigo') }}" maxlength="7" required autocomplete="off" autocapitalize="characters" placeholder="ABC-123" autofocus>
    <div class="footer"><button class="btn">Buscar grupo</button></div>
</form>
@endsection
