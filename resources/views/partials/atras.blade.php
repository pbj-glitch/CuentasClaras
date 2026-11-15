<a href="{{ $href }}" class="iconbtn" aria-label="Volver">
    @if (($icono ?? 'atras') === 'cerrar')
        <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
    @else
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    @endif
</a>
