@extends('layouts.app')

@section('titulo', 'Mis Grupos · Cuentas Claras')

@section('topbar')
<div class="topbar">
    <div>
        <div class="eyebrow">Hola, {{ auth()->user()->name }} 👋</div>
        <h1>Mis Grupos</h1>
    </div>
    <span class="av lg c1">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
</div>
@endsection

@section('content')
@php $total = $teDeben - $debes; @endphp

<div class="home-grid">
<div class="hero">
    <div class="lbl">Tu saldo total</div>
    <div class="big">{{ pesos($total, signo: true) }}</div>
    <div class="row">
        <div class="box"><small>Te deben</small><b class="up">{{ pesos($teDeben) }}</b></div>
        <div class="box"><small>Debes</small><b class="down">{{ pesos($debes) }}</b></div>
    </div>
</div>

<div class="pad">
    @if ($pendientes->isNotEmpty())
        <div class="section-title"><h2>Esperando aprobación</h2></div>
        @foreach ($pendientes as $solicitud)
            <div class="card group">
                <div class="cover">{{ $solicitud->grupo->inicial() }}</div>
                <div class="info">
                    <div class="name">{{ $solicitud->grupo->nombre }}</div>
                    <div class="meta">Solicitud enviada {{ $solicitud->created_at->diffForHumans() }}</div>
                </div>
                <span class="pill zero">⏳ Pendiente</span>
            </div>
        @endforeach
    @endif

    <div class="section-title">
        <h2>Activos</h2>
        <a href="{{ route('grupos.create') }}">+ Nuevo grupo</a>
    </div>

    <div class="grupos-lista">
    @forelse ($grupos as $grupo)
        <a href="{{ route('grupos.show', $grupo) }}" class="card group">
            <div class="cover">{{ $grupo->inicial() }}</div>
            <div class="info">
                <div class="name">{{ $grupo->nombre }}</div>
                <div class="meta">{{ $grupo->miembros->count() }} {{ Str::plural('persona', $grupo->miembros->count()) }} · {{ $grupo->gastos->count() }} {{ Str::plural('gasto', $grupo->gastos->count()) }}</div>
                <div class="stack">
                    @foreach ($grupo->miembros->take(5) as $m)
                        @include('partials.av', ['m' => $m, 'size' => 'sm'])
                    @endforeach
                    @if ($grupo->miembros->count() > 5)
                        <span class="av sm c6">+{{ $grupo->miembros->count() - 5 }}</span>
                    @endif
                </div>
            </div>
            <div class="right">
                <span class="total">{{ pesos($grupo->total()) }}</span>
                @if ($grupo->mi_saldo > 0)
                    <span class="pill pos">Te deben {{ pesos($grupo->mi_saldo) }}</span>
                @elseif ($grupo->mi_saldo < 0)
                    <span class="pill neg">Debes {{ pesos(-$grupo->mi_saldo) }}</span>
                @else
                    <span class="pill zero">A mano ✓</span>
                @endif
            </div>
        </a>
    @empty
        <div class="card vacio">
            <div class="e">🧾</div>
            <h3>Todavía no tienes grupos</h3>
            <p>Crea uno para un viaje, el depto o un carrete, o únete con el código que te mandó un amigo.</p>
            <a href="{{ route('grupos.create') }}" class="btn">Crear grupo</a>
            <a href="{{ route('unirse.index') }}" class="btn ghost" style="margin-top:8px">Tengo un código</a>
        </div>
    @endforelse
    </div>
</div>
</div>
@endsection
