@extends('layouts.app')

@section('ancho', 'angosto')
@section('titulo', 'Nuevo grupo · Cuentas Claras')
@section('sin_tabbar', true)

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.index'), 'icono' => 'cerrar'])
    <div class="title">Nuevo grupo</div>
    <div class="espacio"></div>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('grupos.store') }}" class="pad" style="margin-top:16px">
    @csrf
    <div class="field">
        <label for="nombre">Nombre del grupo</label>
        <input class="input" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="40" placeholder="Ej: Viaje a Pucón" autofocus>
    </div>

    <div class="field">
        <label>¿Quiénes más están? <span style="text-transform:none;font-weight:500">(opcional)</span></label>
        <p class="muted" style="font-size:12px;margin:-2px 0 10px;line-height:1.4">
            Puedes anotar gastos a su nombre aunque no tengan la app. Cuando se unan con el código, se quedan con su saldo.
        </p>
        <div id="integrantes">
            @foreach (old('integrantes', ['', '']) as $nombre)
                <input class="input" name="integrantes[]" value="{{ $nombre }}" maxlength="30" placeholder="Nombre" style="margin-bottom:8px">
            @endforeach
        </div>
        <button type="button" class="link" style="background:none;border:0" onclick="agregarIntegrante()">+ Agregar otra persona</button>
    </div>

    <button class="btn" style="margin-top:10px">Crear grupo</button>
</form>
@endsection

@push('scripts')
<script>
    function agregarIntegrante() {
        const input = document.createElement('input');
        Object.assign(input, { className: 'input', name: 'integrantes[]', maxLength: 30, placeholder: 'Nombre' });
        input.style.marginBottom = '8px';
        document.getElementById('integrantes').appendChild(input);
        input.focus();
    }
</script>
@endpush
