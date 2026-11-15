@extends('layouts.app')

@section('ancho', 'angosto')
@section('titulo', 'Unirse a '.$grupo->nombre)

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('unirse.index')])
    <div class="title">Unirse a un grupo</div>
    <div class="espacio"></div>
</div>
@endsection

@section('content')
<div class="intro">
    <h1>¡Código válido!</h1>
    <p>Te invitaron a este grupo:</p>
</div>

<div class="card found">
    <div class="top">
        <div class="emoji">{{ $grupo->inicial() }}</div>
        <div>
            <b>{{ $grupo->nombre }}</b>
            <small>{{ $grupo->miembros->count() }} personas · {{ $grupo->gastos->count() }} gastos · {{ pesos($grupo->total()) }}</small>
        </div>
    </div>
</div>

@if ($pendiente)
    <div class="pad">
        <div class="card vacio" style="margin-top:16px">
            <div class="e">⏳</div>
            <h3>Ya enviaste tu solicitud</h3>
            <p>Apenas alguien del grupo te acepte, lo vas a ver en Mis Grupos.</p>
            <a href="{{ route('grupos.index') }}" class="btn ghost">Volver a Mis Grupos</a>
        </div>
    </div>
@else
    <form method="POST" action="{{ route('solicitudes.store', $grupo) }}" class="pad">
        @csrf

        @if ($sinCuenta->isNotEmpty())
            <div class="who-q">¿Quién eres en este grupo?</div>
            <div class="who-s">Ya hay gastos anotados a nombre de estas personas. Si eres una de ellas, elígela para quedarte con su saldo.</div>
        @endif

        @foreach ($sinCuenta as $m)
            <label class="opcion">
                <input type="radio" name="miembro" value="{{ $m->id }}" required @checked(Str::lower($m->nombre_alias) === Str::lower(auth()->user()->name))>
                @include('partials.av', ['m' => $m])
                <div class="t">
                    <b>{{ $m->nombre_alias }}</b>
                    <small>Agregado al grupo · saldo {{ pesos($saldos[$m->id] ?? 0, signo: true) }}</small>
                </div>
                <span class="radio"></span>
            </label>
        @endforeach

        <label class="opcion">
            <input type="radio" name="miembro" value="nuevo" required @checked($sinCuenta->isEmpty())>
            <span class="av c6" style="font-size:18px">+</span>
            <div class="t"><b>Soy nuevo en el grupo</b><small>Entras como {{ auth()->user()->name }} con saldo $0</small></div>
            <span class="radio"></span>
        </label>

        <div style="margin-top:14px">
            <button class="btn">Pedir unirme</button>
        </div>
    </form>
@endif
@endsection
