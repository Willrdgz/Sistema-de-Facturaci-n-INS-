<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            @yield('title', 'InVenta')
        </title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @php
            $layoutBusiness = \App\Models\Business::first() ?? new \App\Models\Business;
            $layoutColors = $layoutBusiness->layoutColors();
        @endphp
        <style>
            :root {
                --logo-invert: {{ \App\Models\Business::contrastColor($layoutColors['sidebar']) === '#ffffff' ? 1 : 0 }};
                @foreach($layoutColors as $key => $color)
                    --layout-{{ $key }}: {{ $color }};
                    --layout-{{ $key }}-text: {{ \App\Models\Business::contrastColor($color) }};
                @endforeach
            }
        </style>
    </head>
    <body class="app-layout bg-slate-100 text-slate-800">
        <div class="min-h-screen lg:flex">
            <aside class="layout-sidebar bg-slate-950 text-white lg:w-64 lg:shrink-0 p-5">
                <div class="mb-8">
                    <a href="{{ route('dashboard') }}" class="block max-w-64 p-3" aria-label="InVenta — ir al panel principal">
                        <img src="{{ asset('images/inventa-logo.png') }}" alt="InVenta" class="layout-logo block w-full h-auto">
                    </a>
                    <small class="block mt-3 text-slate-400">
                        {{ $layoutBusiness->trade_name ?? $layoutBusiness->name ?? 'Mi negocio' }}
                    </small>
                </div>
                <nav class="grid grid-cols-2 gap-2 lg:grid-cols-1">
                    @foreach([['dashboard','Panel','⌂'],['sales.index','Ventas','▤'],['customers.index','Clientes','♙'],['products.index','Productos','□'],['inventory.index','Inventario','↕'],['categories.index','Categorías','◇'],['reports.index','Reportes','▥'],['users.index','Usuarios','♙'],['business.edit','Configuración','⚙']] as [$route,$label,$icon])
                        @if(!in_array($route,['categories.index','reports.index','users.index','business.edit']) || auth()->user()->role==='admin')
                            <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(str_replace('.index','.*',$route)) ? 'nav-active' : '' }}">
                                <span>
                                    {{ $icon }}
                                </span>
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </nav>
                <div class="sidebar-account mt-8 rounded-2xl p-4 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="sidebar-avatar grid h-10 w-10 shrink-0 place-items-center rounded-xl text-lg font-bold" aria-hidden="true">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs opacity-70">Mi cuenta</p>
                            <p class="break-words font-semibold leading-5">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <span class="sidebar-role mt-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z" />
                            <path d="m8 12 3 3 5-6" />
                        </svg>
                        {{ auth()->user()->role==='admin'?'Administrador':'Vendedor' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button class="sidebar-logout flex w-full items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-xs font-semibold transition" type="submit">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M9 5H5v14h4M9 12h11m-4-4 4 4-4 4" />
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </aside>
            <main class="flex-1 min-w-0">
                <header class="layout-header flex items-center justify-between border-b bg-white px-5 py-4 lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600">
                            Sistema de gestión
                        </p>
                        <h1 class="text-xl font-bold">
                            @yield('heading', 'Panel principal')
                        </h1>
                    </div>
                    <a href="{{ route('sales.create') }}" class="btn-primary">
                        + Nueva venta
                    </a>
                </header>
                <div class="p-5 lg:p-8">
                    @if(session('success'))
                        <div class="alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert-error">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert-error">
                            <strong>
                                Revisa la información:
                            </strong>
                            <ul class="ml-5 list-disc">
                                @foreach($errors->all() as $error)
                                    <li>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
