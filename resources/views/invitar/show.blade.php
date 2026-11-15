@extends('layouts.app')

@section('titulo', 'Invitar · '.$grupo->nombre)
@section('accion_mas', route('gastos.create', $grupo))
@section('grupo_saldos', route('grupos.saldos', $grupo))
@section('estado_url', route('grupos.estado', $grupo))

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.show', $grupo)])
    <div class="title">Invitar al grupo</div>
    <div class="espacio"></div>
</div>
@endsection

@section('content')
@php
    $link = route('unirse.show', $grupo->codigo_invitacion);
    $mensaje = "¡Únete a \"{$grupo->nombre}\" en Cuentas Claras! Entra a {$link} o usa el código {$grupo->codigo_invitacion}";
@endphp

<div class="seg">
    <a href="{{ route('grupos.show', $grupo) }}">Gastos</a>
    <a href="{{ route('grupos.saldos', $grupo) }}">Saldos</a>
    <a class="on">Invitar</a>
</div>

<div class="cols">
<div class="col-side">
<div class="qrcard">
    <div class="g"><span>{{ $grupo->inicial() }}</span> {{ $grupo->nombre }}</div>
    <div class="sub">Escanea con la cámara para unirte al grupo</div>
    <div class="qrbox">
        {!! \App\Services\QrService::svg($link) !!}
        <span class="logo">$</span>
    </div>
    <div class="code-lbl">Código del grupo</div>
    <div class="code">
        @foreach (str_split(str_replace('-', '', $grupo->codigo_invitacion)) as $i => $letra)
            @if ($i === 3)<i>–</i>@endif
            <b>{{ $letra }}</b>
        @endforeach
    </div>
</div>

<div class="share">
    <a href="https://wa.me/?text={{ rawurlencode($mensaje) }}" target="_blank"><span class="ic" style="background:#DFF5E4">💬</span>WhatsApp</a>
    <button type="button" onclick="copiar(@js($link), this)"><span class="ic" style="background:#E2ECF8">🔗</span>Copiar link</button>
    <button type="button" onclick="copiar(@js($grupo->codigo_invitacion), this)"><span class="ic" style="background:#F3E8FB">📋</span>Copiar código</button>
</div>
</div>

<div class="pad col-main">
    @if ($solicitudes->isNotEmpty())
        <div class="section-title"><h2>Solicitudes <span class="badge">{{ $solicitudes->count() }}</span></h2></div>
        <div class="card">
            @foreach ($solicitudes as $solicitud)
                <div class="row">
                    <span class="av c6">{{ mb_strtoupper(mb_substr($solicitud->user->name, 0, 1)) }}</span>
                    <div class="t">
                        <b>{{ $solicitud->user->name }}</b>
                        <small>
                            {{ $solicitud->created_at->diffForHumans() }}
                            @if ($solicitud->miembroReclamado) · dice ser <b style="display:inline">{{ $solicitud->miembroReclamado->nombre_alias }}</b> @endif
                        </small>
                    </div>
                    <div class="acts">
                        <form method="POST" action="{{ route('solicitudes.rechazar', [$grupo, $solicitud]) }}">@csrf<button class="no" aria-label="Rechazar">✕</button></form>
                        <form method="POST" action="{{ route('solicitudes.aceptar', [$grupo, $solicitud]) }}">@csrf<button class="si" aria-label="Aceptar">✓</button></form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="section-title"><h2>Integrantes sin cuenta</h2></div>
    <div class="card">
        @foreach ($sinCuenta as $m)
            <div class="row">
                @include('partials.av', ['m' => $m])
                <div class="t"><b>{{ $m->nombre_alias }}</b><small>Al unirse con el código se queda con su saldo</small></div>
            </div>
        @endforeach
        <form method="POST" action="{{ route('miembros.store', $grupo) }}" class="inline-form">
            @csrf
            <input class="input" name="nombre_alias" required maxlength="30" placeholder="Agregar a alguien sin app…">
            <button class="btn">Agregar</button>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    function copiar(texto, boton) {
        navigator.clipboard.writeText(texto).then(() => {
            const original = boton.lastChild.textContent;
            boton.lastChild.textContent = '¡Copiado!';
            setTimeout(() => boton.lastChild.textContent = original, 1500);
        });
    }
</script>
@endpush
