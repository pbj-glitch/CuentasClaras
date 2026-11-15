@extends('layouts.app')

@section('titulo', 'Crear cuenta · Cuentas Claras')
@section('sin_tabbar', true)

@section('content')
<div class="auth-wrap">
@include('partials.auth-marca')
<div class="auth">
    <div class="logo-grande">$</div>
    <h1>Crea tu cuenta</h1>
    <p class="sub">Tu nombre es el que verán tus amigos en los grupos.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
            <label for="name">Nombre</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required maxlength="40" autofocus placeholder="Ej: Benja">
        </div>
        <div class="field">
            <label for="email">Correo</label>
            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </div>
        <div class="field">
            <label for="password">Contraseña</label>
            <input class="input" type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
        </div>
        <div class="field">
            <label for="password_confirmation">Repite la contraseña</label>
            <input class="input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button class="btn" style="margin-top:8px">Crear cuenta</button>
    </form>

    <p class="alt">¿Ya tienes cuenta? <a class="link" href="{{ route('login') }}">Entra</a></p>
</div>
</div>
@endsection
