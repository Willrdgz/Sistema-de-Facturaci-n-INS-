<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'INS Facturación')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
<div class="min-h-screen lg:flex">
    <aside class="bg-slate-950 text-white lg:w-64 p-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 mb-8"><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-500 font-black">INS</span><span><strong class="block">INS Facturación</strong><small class="text-slate-400">El Progreso</small></span></a>
        <nav class="grid grid-cols-2 gap-2 lg:grid-cols-1">
            @foreach([['dashboard','Panel','⌂'],['sales.index','Ventas','▤'],['customers.index','Clientes','♙'],['products.index','Productos','□'],['categories.index','Categorías','◇']] as [$route,$label,$icon])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(str_replace('.index','.*',$route)) ? 'nav-active' : '' }}"><span>{{ $icon }}</span>{{ $label }}</a>
            @endforeach
        </nav>
    </aside>
    <main class="flex-1">
        <header class="flex items-center justify-between border-b bg-white px-5 py-4 lg:px-8"><div><p class="text-xs font-semibold uppercase tracking-widest text-emerald-600">Sistema de gestión</p><h1 class="text-xl font-bold">@yield('heading', 'Panel principal')</h1></div><a href="{{ route('sales.create') }}" class="btn-primary">+ Nueva venta</a></header>
        <div class="p-5 lg:p-8">
            @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert-error"><strong>Revisa la información:</strong><ul class="ml-5 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
