<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F4F3EE">
    <title>@yield('titulo', 'Cuentas Claras')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/navegacion.js') }}"></script>
    {{-- Precarga: el menú se baja apenas abre la página; cualquier otro link se deja listo al apuntarlo o tocarlo --}}
    <script type="speculationrules">
    {
        "prefetch": [{
            "where": { "selector_matches": ".tabbar a, .side-nav a, .sidebar .btn" },
            "eagerness": "eager"
        }],
        "prerender": [{
            "where": { "and": [
                { "href_matches": "/*" },
                { "not": { "selector_matches": "[download], [target=_blank], [data-sin-precarga]" } }
            ]},
            "eagerness": "moderate"
        }]
    }
    </script>
</head>
<body>
@php $grupoNav = request()->route('grupo'); @endphp
{{-- data-estado: URL que polling.js consulta cada 3 s para refrescar la vista (tiempo real) --}}
<div class="app @hasSection('sin_tabbar') sin-tabbar @endif @auth con-sidebar @endauth" @hasSection('estado_url') data-estado="@yield('estado_url')" @endif>
    @auth
        {{-- Navegación de escritorio (en celular se usa la barra inferior) --}}
        <aside class="sidebar">
            <a href="{{ route('grupos.index') }}" class="marca"><span class="logo-chico">$</span>Cuentas Claras</a>

            <nav class="side-nav">
                <a href="{{ route('grupos.index') }}" class="{{ request()->routeIs('grupos.index') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>Mis grupos
                </a>
                <a href="{{ route('unirse.index') }}" class="{{ request()->routeIs('unirse.*') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 17h3v3"/></svg>Unirse con código
                </a>
                <a href="{{ route('grupos.create') }}" class="{{ request()->routeIs('grupos.create') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Nuevo grupo
                </a>
            </nav>

            @if ($grupoNav instanceof \App\Models\Grupo)
                <div class="side-grupo">
                    <div class="side-titulo">
                        <span class="emoji">{{ $grupoNav->inicial() }}</span>
                        <div><b>{{ $grupoNav->nombre }}</b><small>{{ $grupoNav->miembros->count() }} personas</small></div>
                    </div>
                    <nav class="side-nav">
                        <a href="{{ route('grupos.show', $grupoNav) }}" class="{{ request()->routeIs('grupos.show') ? 'on' : '' }}">
                            <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>Gastos
                        </a>
                        <a href="{{ route('grupos.saldos', $grupoNav) }}" class="{{ request()->routeIs('grupos.saldos') ? 'on' : '' }}">
                            <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>Saldos
                        </a>
                        <a href="{{ route('invitar.show', $grupoNav) }}" class="{{ request()->routeIs('invitar.*') ? 'on' : '' }}">
                            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21c1-4 3.5-6 7-6s6 2 7 6M19 8v6M16 11h6"/></svg>Invitar
                        </a>
                    </nav>
                    <a href="{{ route('gastos.create', $grupoNav) }}" class="btn sm">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Agregar gasto
                    </a>
                </div>
            @endif

            <div class="side-user">
                <span class="av lg c1">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="t"><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->email }}</small></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="iconbtn" aria-label="Salir" title="Salir"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg></button>
                </form>
            </div>
        </aside>
    @endauth

    <main class="main @yield('ancho')">
        @yield('topbar')

        @if (session('ok'))
            <div class="flash">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash error">
                @if ($errors->count() === 1)
                    {{ $errors->first() }}
                @else
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    @auth
        @unless (View::hasSection('sin_tabbar'))
            <nav class="tabbar">
                <a href="{{ route('grupos.index') }}" class="tab {{ request()->routeIs('grupos.index') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>Grupos
                </a>
                <a href="{{ route('unirse.index') }}" class="tab {{ request()->routeIs('unirse.*') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 17h3v3"/></svg>Unirse
                </a>
                <a href="@yield('accion_mas', route('grupos.create'))" class="tab add" aria-label="Agregar">
                    <span class="b"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span>
                </a>
                @hasSection('grupo_saldos')
                    <a href="@yield('grupo_saldos')" class="tab {{ request()->routeIs('grupos.saldos') ? 'on' : '' }}">
                        <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>Saldos
                    </a>
                @else
                    <span class="tab" style="opacity:.35"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>Saldos</span>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="tab"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>Salir</button>
                </form>
            </nav>
        @endunless
    @endauth
</div>
@hasSection('estado_url')<script src="{{ asset('js/polling.js') }}" defer></script>@endif
@stack('scripts')
</body>
</html>
