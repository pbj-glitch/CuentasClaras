(function () {
    const app = document.querySelector('.app[data-estado]');
    if (!app) return;

    const url = app.dataset.estado;
    let versionActual = null;
    let recargando = false;

    async function verificarEstado() {
        if (document.hidden || recargando) return;

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                cache: 'no-store'
            });

            if(!res.ok) return;
            const data = await res.json();

            if (versionActual === null) {
                versionActual = data.version;
            } else if (DataTransfer.version && data.version !== versionActual) {
                recargando = true;
                mostrarAviso('Hay cambios nuevos en el grupo. Actualizando...');
                setTimeout(() => window.location.reload(), 900);
            }
        } catch (e) {
            // ignoraar errores de red temporales
        }
    }

    function mostrarAviso(texto) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = texto;
        document.body.appendChild(toast);
    }

    verificarEstado();
    setInterval(verificarEstado, 3000);
})();