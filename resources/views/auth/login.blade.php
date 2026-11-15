@extends('layouts.app')

@section('titulo', 'Entrar · Cuentas Claras')
@section('sin_tabbar', true)

@section('content')
<div class="auth-wrap">
@include('partials.auth-marca')
<div class="auth">
    <div class="logo-grande">$</div>
    <h1>Cuentas Claras</h1>
    <p class="sub">Los gastos del grupo, sin calculadora.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label for="email">Correo</label>
            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>
        <div class="field">
            <label for="password">Contraseña</label>
            <input class="input" type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn" style="margin-top:8px">Entrar</button>
    </form>

    <p class="alt">¿No tienes cuenta? <a class="link" href="{{ route('register') }}">Créala aquí</a></p>
</div>
</div>
@endsection
