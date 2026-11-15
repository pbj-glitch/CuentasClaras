/**
 * Navegación con sensación de app:
 * - Las páginas se precargan antes del toque (reglas de especulación en el layout),
 *   así que al tocar ya están listas.
 * - Las transiciones entre vistas las hace el navegador (@view-transition en app.css);
 *   aquí solo marcamos si se va "atrás" para que la animación vaya al revés.
 * - Si una vista igual se demora (red lenta), aparece una barrita de carga arriba.
 */
(function () {
    // Dirección de la animación: atrás = volver en el historial
    window.addEventListener('pagereveal', e => {
        if (!e.viewTransition || !window.navigation || !navigation.activation) return;
        const { from, entry, navigationType } = navigation.activation;
        if (navigationType === 'traverse' && from && entry && entry.index < from.index) {
            e.viewTransition.types.add('atras');
        }
    });

    // Barrita de carga solo si la página no llega altiro
    let timer;
    function cargando() {
        clearTimeout(timer);
        timer = setTimeout(() => document.documentElement.classList.add('navegando'), 150);
    }
    function listo() {
        clearTimeout(timer);
        document.documentElement.classList.remove('navegando');
    }

    document.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (a.target && a.target !== '_self') return;
        if (a.hasAttribute('download') || a.origin !== location.origin) return;
        if (a.pathname === location.pathname && a.search === location.search && a.hash) return;
        cargando();
    });
    document.addEventListener('submit', e => { if (!e.defaultPrevented) cargando(); });

    // Navegadores sin reglas de especulación: precargar con <link rel=prefetch>
    if (!(HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules'))) {
        const pedidas = new Set([location.href]);
        function precargar(a) {
            if (!a || a.origin !== location.origin || a.hash || a.hasAttribute('download') || a.matches('[data-sin-precarga], [target=_blank]')) return;
            if (/\/foto$/.test(a.pathname) || pedidas.has(a.href)) return;
            pedidas.add(a.href);
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = a.href;
            document.head.appendChild(link);
        }
        let hover;
        document.addEventListener('pointerover', e => {
            clearTimeout(hover);
            const a = e.target.closest('a[href]');
            if (a) hover = setTimeout(() => precargar(a), 80);
        });
        document.addEventListener('touchstart', e => precargar(e.target.closest('a[href]')), { passive: true });
        document.addEventListener('DOMContentLoaded', () => {
            const menu = () => document.querySelectorAll('.tabbar a, .side-nav a, .sidebar .btn').forEach(precargar);
            (window.requestIdleCallback || setTimeout)(menu);
        });
    }

    // Al volver con el botón atrás la página sale del caché tal cual: sacar la barrita
    window.addEventListener('pageshow', listo);
})();
