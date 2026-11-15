@php $marcados = array_map('intval', (array) ($item['miembros'] ?? [])); @endphp
<div class="item">
    <div class="fila">
        <input class="q" type="number" name="items[{{ $i }}][cantidad]" value="{{ $item['cantidad'] ?? 1 }}" min="1" max="999" aria-label="Cantidad">
        <input class="n" name="items[{{ $i }}][nombre]" value="{{ $item['nombre'] ?? '' }}" placeholder="Producto" required maxlength="120">
        <input class="p" type="number" name="items[{{ $i }}][precio_unitario]" value="{{ $item['precio_unitario'] ?? '' }}" min="1" placeholder="$ c/u" required aria-label="Precio unitario">
        <button type="button" class="quitar" aria-label="Quitar ítem">✕</button>
    </div>
    <div class="quien">
        <button type="button" class="todos pill zero">Todos</button>
        @foreach ($grupo->miembros as $m)
            <label>
                <input type="checkbox" name="items[{{ $i }}][miembros][]" value="{{ $m->id }}" @checked(in_array($m->id, $marcados, true))>
                <span>@include('partials.av', ['m' => $m]){{ $m->id === $yo->id ? 'Yo' : $m->nombre_alias }}</span>
            </label>
        @endforeach
    </div>
</div>
