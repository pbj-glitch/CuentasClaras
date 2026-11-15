@extends('layouts.app')

@section('titulo', 'Saldos · '.$grupo->nombre)
@section('accion_mas', route('gastos.create', $grupo))
@section('grupo_saldos', route('grupos.saldos', $grupo))
@section('estado_url', route('grupos.estado', $grupo))

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.show', $grupo)])
    <div class="title">{{ $grupo->nombre }}</div>
    <button class="iconbtn" aria-label="Compartir" onclick="compartir()">
        <svg viewBox="0 0 24 24"><path d="M4 12v8h16v-8M12 3v13M7 8l5-5 5 5"/></svg>
    </button>
</div>
@endsection

@section('content')
@php $nombre = fn ($id) => $id === $yo->id ? 'Tú' : $grupo->miembros->firstWhere('id', $id)->nombre_alias; @endphp

<div class="seg">
    <a href="{{ route('grupos.show', $grupo) }}">Gastos</a>
    <a class="on">Saldos</a>
    <a href="{{ route('invitar.show', $grupo) }}">Invitar</a>
</div>

<div class="pad saldos-grid">
    <section class="a-bal">
    <div class="section-title"><h2>Balance de cada uno</h2></div>
    <div class="card bars">
        @foreach ($saldos as $id => $saldo)
            @php $m = $grupo->miembros->firstWhere('id', $id); @endphp
            <div class="bar">
                <span class="who">@include('partials.av', ['m' => $m, 'size' => 'sm']){{ $nombre($id) }}</span>
                <div class="track">
                    @if ($saldo != 0)
                        <div class="fill {{ $saldo > 0 ? 'pos' : 'neg' }}" style="width:{{ round(abs($saldo) / $maximo * 50, 1) }}%"></div>
                    @endif
                </div>
                <span class="v money {{ $saldo > 0 ? 'pos' : ($saldo < 0 ? 'neg' : 'muted') }}">{{ pesos($saldo, signo: true) }}</span>
            </div>
        @endforeach
    </div>
    </section>

    <section class="a-tr">
    <div class="section-title">
        <h2>Para quedar a mano</h2>
        @if ($transferencias) <a href="#" onclick="compartir(); return false;">Compartir</a> @endif
    </div>
    <div class="card">
        @if ($transferencias)
            <div class="settle-head">
                <div class="ic">⚡</div>
                <div>
                    <b>{{ count($transferencias) === 1 ? 'Solo 1 transferencia' : 'Solo '.count($transferencias).' transferencias' }}</b>
                    <small>en vez de saldar los {{ $grupo->gastos->count() }} gastos uno por uno</small>
                </div>
            </div>
            @foreach ($transferencias as $t)
                @php
                    $de = $grupo->miembros->firstWhere('id', $t['de']);
                    $a = $grupo->miembros->firstWhere('id', $t['a']);
                    $meToca = $t['a'] === $yo->id;
                @endphp
                <div class="tr">
                    @include('partials.av', ['m' => $de])
                    <span class="arrow">›</span>
                    @include('partials.av', ['m' => $a])
                    <span class="txt">
                        {{ $nombre($t['de']) }} → {{ $meToca ? 'a ti' : $a->nombre_alias }}
                        <span class="money {{ $meToca ? 'pos' : '' }}">{{ pesos($t['monto']) }}</span>
                    </span>
                    <form method="POST" action="{{ route('pagos.store', $grupo) }}">
                        @csrf
                        <input type="hidden" name="de_miembro_id" value="{{ $t['de'] }}">
                        <input type="hidden" name="para_miembro_id" value="{{ $t['a'] }}">
                        <input type="hidden" name="monto" value="{{ $t['monto'] }}">
                        <button class="mark {{ $meToca ? 'me' : '' }}">{{ $meToca ? 'Marcar recibido' : 'Marcar pagado' }}</button>
                    </form>
                </div>
            @endforeach
        @else
            <div class="settle-head">
                <div class="ic">🎉</div>
                <div><b>¡Están todos a mano!</b><small>Nadie le debe nada a nadie.</small></div>
            </div>
        @endif
    </div>
    </section>

    @if ($grupo->pagos->isNotEmpty())
    <section class="a-pagos">
        <div class="section-title"><h2>Pagos registrados</h2></div>
        <div class="card">
            @foreach ($grupo->pagos as $pago)
                <div class="tr">
                    @include('partials.av', ['m' => $pago->deMiembro, 'size' => 'sm'])
                    <span class="arrow">›</span>
                    @include('partials.av', ['m' => $pago->paraMiembro, 'size' => 'sm'])
                    <span class="txt">
                        {{ $nombre($pago->de_miembro_id) }} → {{ $pago->para_miembro_id === $yo->id ? 'a ti' : $pago->paraMiembro->nombre_alias }} · {{ $pago->created_at->diffForHumans() }}
                        <span class="money">{{ pesos($pago->monto) }} ✓</span>
                    </span>
                    <form method="POST" action="{{ route('pagos.destroy', [$grupo, $pago]) }}">
                        @csrf @method('DELETE')
                        <button class="deshacer">Deshacer</button>
                    </form>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
@php
    // Texto listo para pegar en WhatsApp
    $resumen = "💸 {$grupo->nombre}: para quedar a mano\n";
    foreach ($transferencias as $t) {
        $resumen .= '• '.$grupo->miembros->firstWhere('id', $t['de'])->nombre_alias.' → '
            .$grupo->miembros->firstWhere('id', $t['a'])->nombre_alias.': '.pesos($t['monto'])."\n";
    }
@endphp
<script>
    const resumen = @json($resumen);

    function compartir() {
        if (navigator.share) {
            navigator.share({ text: resumen }).catch(() => {});
        } else {
            navigator.clipboard.writeText(resumen).then(() => alert('Resumen copiado. Pégalo en WhatsApp.'));
        }
    }
</script>
@endpush
