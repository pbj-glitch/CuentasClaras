@extends('layouts.app')

@section('titulo', 'Nuevo gasto · '.$grupo->nombre)
@section('sin_tabbar', true)

@section('topbar')
<div class="topbar">
    @include('partials.atras', ['href' => route('grupos.show', $grupo), 'icono' => 'cerrar'])
    <div class="title">Nuevo gasto · {{ $grupo->nombre }}</div>
    <div class="espacio"></div>
</div>
@endsection

@section('content')
@php $items = old('items', $items); @endphp

<form method="POST" action="{{ route('gastos.store', $grupo) }}" id="form-gasto">
    @csrf
    <div class="pad">
        {{-- Aquí va la opción de leer la boleta con IA (foto o texto) → GroqController --}}

        <div class="opts">
            <div class="opt full">
                <small>Comercio o descripción</small>
                <input name="comercio" value="{{ old('comercio') }}" required maxlength="80" placeholder="Ej: Cena en La Maga">
            </div>
            <div class="opt">
                <small>Pagó</small>
                <select name="pagado_por_miembro_id">
                    @foreach ($grupo->miembros as $m)
                        <option value="{{ $m->id }}" @selected(old('pagado_por_miembro_id', $yo->id) == $m->id)>{{ $m->id === $yo->id ? 'Yo ('.$m->nombre_alias.')' : $m->nombre_alias }}</option>
                    @endforeach
                </select>
            </div>
            <div class="opt">
                <small>Propina ($)</small>
                <input type="number" name="propina" id="propina" value="{{ old('propina', 0) }}" min="0">
            </div>
        </div>
        <div class="quien" id="propina-rapida" style="margin-top:8px">
            @foreach ([0, 10, 15] as $pct)
                <button type="button" class="pill zero" data-pct="{{ $pct }}">{{ $pct ? $pct.'%' : 'Sin propina' }}</button>
            @endforeach
        </div>

        <div class="section-title">
            <h2>Ítems</h2>
            <a href="#" id="agregar-item">+ Agregar ítem</a>
        </div>
        <p class="muted" style="font-size:12px;margin:-4px 0 10px">Marca quién consumió cada cosa. Lo compartido se divide en partes iguales y la propina en proporción a lo que consumió cada uno.</p>

        <div class="card" id="items">
            @foreach ($items as $i => $item)
                @include('gastos.partials.item', ['i' => $i, 'item' => $item])
            @endforeach
        </div>
    </div>

    <div class="footer">
        <button class="btn" id="btn-guardar">Guardar gasto</button>
    </div>
</form>

<template id="tpl-item">
    @include('gastos.partials.item', ['i' => '__I__', 'item' => ['nombre' => '', 'cantidad' => 1, 'precio_unitario' => '', 'miembros' => $grupo->miembros->pluck('id')->all()]])
</template>
@endsection

@push('scripts')
<script>
    const contenedor = document.getElementById('items');
    const propina = document.getElementById('propina');
    let siguiente = {{ count($items) }} + 100;
    const pesos = n => '$' + Math.round(n).toLocaleString('es-CL');

    function subtotal() {
        return [...contenedor.querySelectorAll('.item')].reduce((s, fila) =>
            s + (parseInt(fila.querySelector('.q').value) || 0) * (parseInt(fila.querySelector('.p').value) || 0), 0);
    }

    // Total en vivo: suma de ítems + propina
    function recalcular() {
        const total = subtotal() + (parseInt(propina.value) || 0);
        document.getElementById('btn-guardar').textContent = 'Guardar gasto · ' + pesos(total);
    }

    contenedor.addEventListener('input', recalcular);
    propina.addEventListener('input', recalcular);

    // Atajos de propina: calcula el % sobre el subtotal
    document.getElementById('propina-rapida').addEventListener('click', e => {
        const boton = e.target.closest('[data-pct]');
        if (!boton) return;
        propina.value = Math.round(subtotal() * boton.dataset.pct / 100);
        recalcular();
    });

    contenedor.addEventListener('click', e => {
        // Quitar ítem
        if (e.target.closest('.quitar')) {
            if (contenedor.querySelectorAll('.item').length > 1) e.target.closest('.item').remove();
            recalcular();
        }
        // "Todos": marca a todos, o desmarca si ya estaban todos
        if (e.target.closest('.todos')) {
            const cajas = [...e.target.closest('.item').querySelectorAll('input[type=checkbox]')];
            const todas = cajas.every(c => c.checked);
            cajas.forEach(c => c.checked = !todas);
        }
    });

    document.getElementById('agregar-item').addEventListener('click', e => {
        e.preventDefault();
        const html = document.getElementById('tpl-item').innerHTML.replaceAll('__I__', siguiente++);
        contenedor.insertAdjacentHTML('beforeend', html);
        contenedor.lastElementChild.querySelector('.n').focus();
    });

    recalcular();
</script>
@endpush
