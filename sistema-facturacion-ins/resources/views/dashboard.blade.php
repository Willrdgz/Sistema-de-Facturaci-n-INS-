@extends('layouts.app')
@section('title','Panel | INS Facturación')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['Ventas de hoy','$'.number_format($salesToday,2),$salesCount.' operaciones','emerald'],['Clientes activos',$customerCount,'registrados','blue'],['Productos',$productCount,'en catálogo','violet'],['Stock bajo',$lowStockCount,'requieren atención','amber']] as [$label,$value,$detail,$color])
    <article class="card border-l-4 border-{{ $color }}-500"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-black">{{ $value }}</p><p class="mt-1 text-xs text-slate-400">{{ $detail }}</p></article>
    @endforeach
</div>
<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="card xl:col-span-2"><div class="section-heading"><h2>Ventas recientes</h2><a href="{{ route('sales.index') }}">Ver todas</a></div>
        <div class="table-wrap"><table><thead><tr><th>Factura</th><th>Cliente</th><th>Fecha</th><th class="text-right">Total</th></tr></thead><tbody>@forelse($recentSales as $sale)<tr><td><a class="link" href="{{ route('sales.show',$sale) }}">{{ $sale->invoice_number }}</a></td><td>{{ $sale->customer?->name ?? 'Consumidor final' }}</td><td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td><td class="text-right font-bold">${{ number_format($sale->total,2) }}</td></tr>@empty<tr><td colspan="4" class="empty">Aún no hay ventas. Crea la primera.</td></tr>@endforelse</tbody></table></div>
    </section>
    <section class="card"><div class="section-heading"><h2>Alertas de inventario</h2></div>@forelse($lowStockProducts as $product)<div class="flex items-center justify-between border-b py-3 last:border-0"><div><p class="font-semibold">{{ $product->name }}</p><small class="text-slate-400">{{ $product->category->name }}</small></div><span class="badge-warning">{{ $product->stock }} uds.</span></div>@empty<p class="empty">Inventario en orden.</p>@endforelse</section>
</div>
@endsection
