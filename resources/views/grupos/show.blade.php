@extends('layouts.app')

@section('titulo', $grupo->nombre.' · Cuentas Claras')
@section('accion_mas', route('gastos.create', $grupo))
@section('grupo_saldos', route('grupos.saldos', $grupo))
@section('estado_url', route('grupos.estado', $grupo))

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.index')])
    <div class="stack">
        @foreach ($grupo->miembros->take(6) as $m)
            @include('partials.av', ['m' => $m])
        @endforeach
    </div>
    <a href="{{ route('invitar.show', $grupo) }}" class="iconbtn" aria-label="Invitar">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21c1-4 3.5-6 7-6s6 2 7 6M19 8v6M16 11h6"/></svg>
    </a>
</div>
@endsection

@section('content')
@php $miSaldo = $saldos[$yo->id] ?? 0; @endphp

<div class="cols">
<div class="col-side">
<div class="head">
    <div class="t">
        <div class="emoji">{{ $grupo->inicial() }}</div>
        <div>
            <h1>{{ $grupo->nombre }}</h1>
            <div class="sub">{{ $grupo->miembros->count() }} personas · {{ $grupo->gastos->count() }} gastos</div>
        </div>
    </div>
    <div class="nums">
        <div><small>Total del grupo</small><b>{{ pesos($grupo->total()) }}</b></div>
        <div><small>Tu saldo</small><b style="color:{{ $miSaldo < 0 ? '#FFB49F' : '#9CF0CF' }}">{{ pesos($miSaldo, signo: true) }}</b></div>
    </div>
</div>

<div class="seg">
    <a class="on">Gastos</a>
    <a href="{{ route('grupos.saldos', $grupo) }}">Saldos</a>
    <a href="{{ route('invitar.show', $grupo) }}">Invitar</a>
</div>

<div class="balances">
    @foreach ($saldos as $id => $saldo)
        @php $m = $grupo->miembros->firstWhere('id', $id); @endphp
        <div class="bal">
            @include('partials.av', ['m' => $m])
            <div class="n">{{ $m->id === $yo->id ? 'Tú' : $m->nombre_alias }}</div>
            <div class="m money {{ $saldo > 0 ? 'pos' : ($saldo < 0 ? 'neg' : 'muted') }}">{{ pesos($saldo, signo: true) }}</div>
        </div>
    @endforeach
</div>
</div>

<div class="pad col-main">
    @forelse ($gastosPorDia as $dia => $gastos)
        <div class="day">{{ \Illuminate\Support\Carbon::parse($dia)->translatedFormat('l j M') }}</div>
        <div class="card">
            @foreach ($gastos as $gasto)
                @php
                    $miParte = $gasto->parteDe($yo);
                    $pagueYo = $gasto->pagado_por_miembro_id === $yo->id;
                    $entre = $gasto->partes->unique('miembro_id')->count();
                @endphp
                <details class="exp-det" data-id="{{ $gasto->id }}">
                    <summary class="exp">
                        <div class="cat" style="background:#FDEBD8">🧾</div>
                        <div class="info">
                            <div class="name"><span class="txt">{{ $gasto->comercio ?: 'Gasto' }}</span></div>
                            <div class="who">
                                {{ $pagueYo ? 'Pagaste tú' : 'Pagó '.$gasto->pagador->nombre_alias }} ·
                                {{ $gasto->items->count() > 1 ? $gasto->items->count().' ítems' : ($entre === $grupo->miembros->count() ? 'entre todos' : 'entre '.$entre) }}
                            </div>
                        </div>
                        <div class="amt">
                            <div class="money {{ $pagueYo ? 'pos' : '' }}">{{ pesos($gasto->total) }}</div>
                            <div class="mine">
                                @if ($pagueYo)
                                    prestaste {{ pesos($gasto->total - $miParte) }}
                                @elseif ($miParte > 0)
                                    tu parte {{ pesos($miParte) }}
                                @else
                                    no participas
                                @endif
                            </div>
                        </div>
                    </summary>
                    <div class="exp-extra">
                        @foreach ($gasto->items as $item)
                            <div class="linea">
                                <span>{{ $item->cantidad }}× {{ $item->nombre }}
                                    <small class="muted">· {{ $item->partes->map(fn ($p) => $grupo->miembros->firstWhere('id', $p->miembro_id)?->nombre_alias)->implode(', ') }}</small>
                                </span>
                                <span class="money">{{ pesos($item->subtotal) }}</span>
                            </div>
                        @endforeach
                        @if ($gasto->propina)
                            <div class="linea"><span>Propina</span><span class="money">{{ pesos($gasto->propina) }}</span></div>
                        @endif
                        <div class="linea" style="margin-top:6px">
                            <span>
                                @foreach ($gasto->partes->groupBy('miembro_id') as $miembroId => $partes)
                                    {{ $grupo->miembros->firstWhere('id', $miembroId)?->nombre_alias }} {{ pesos($partes->sum('monto_asignado')) }}@if (! $loop->last) · @endif
                                @endforeach
                            </span>
                        </div>
                        <div class="acciones">
                            <form method="POST" action="{{ route('gastos.destroy', [$grupo, $gasto]) }}" onsubmit="return confirm('¿Eliminar este gasto?')">
                                @csrf @method('DELETE')
                                <button>Eliminar</button>
                            </form>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    @empty
        <div class="card vacio" style="margin-top:18px">
            <div class="e">🧾</div>
            <h3>Sin gastos todavía</h3>
            <p>Anota la primera boleta: qué se compró, cuánto costó y quién consumió cada cosa.</p>
            <a href="{{ route('gastos.create', $grupo) }}" class="btn">Agregar el primer gasto</a>
        </div>
    @endforelse
</div>
</div>
@endsection
