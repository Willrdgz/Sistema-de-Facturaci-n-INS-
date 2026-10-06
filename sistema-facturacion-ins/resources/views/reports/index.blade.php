@extends('layouts.app')
@section('heading','Reportes de ventas e inventario')
@section('content')
    <form class="toolbar print:hidden">
        <label>
            Desde
            <input class="input" name="from" type="date" value="{{ $from }}">
        </label>
        <label>
            Hasta
            <input class="input" name="to" type="date" value="{{ $to }}">
        </label>
        <button class="btn-secondary">
            Consultar
        </button>
        <button class="btn-primary" name="format" value="csv">
            Descargar CSV
        </button>
        <button class="btn-secondary" type="button" onclick="window.print()">
            Imprimir
        </button>
    </form>
    <div class="grid gap-4 md:grid-cols-2 mb-6">
        <div class="card">
            <p>
                Ventas vigentes del período
            </p>
            <strong class="text-3xl">
                $
                {{ number_format($revenue,2) }}
            </strong>
        </div>
        <div class="card">
            <p>
                Valor actual del inventario (costo)
            </p>
            <strong class="text-3xl">
                $
                {{ number_format($stockValue,2) }}
            </strong>
        </div>
    </div>
    <section class="card mb-6" aria-labelledby="sales-chart-heading">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h2 id="sales-chart-heading" class="text-lg font-bold">Evolución de las ventas</h2>
                <p class="text-sm text-slate-500">
                    {{ $bucketDays === 1 ? 'Total por día' : 'Total por períodos de '.$bucketDays.' días' }} · Solo ventas vigentes · USD
                </p>
            </div>
            <span class="text-sm font-semibold text-slate-600">{{ array_sum(array_column($chart, 'count')) }} ventas</span>
        </div>
        @if(array_sum(array_column($chart, 'count')) > 0)
            @php
                $chartMax = max(1, max(array_column($chart, 'total')));
                $chartStep = 820 / count($chart);
                $labelEvery = (int) ceil(count($chart) / 8);
            @endphp
            <div class="overflow-x-auto">
                <svg viewBox="0 0 960 310" class="report-chart" role="img" aria-labelledby="sales-chart-title sales-chart-description">
                    <title id="sales-chart-title">Ventas vigentes del período seleccionado</title>
                    <desc id="sales-chart-description">
                        @foreach($chart as $point)
                            {{ $point['period'] }}: ${{ number_format($point['total'], 2) }}, {{ $point['count'] }} ventas.
                        @endforeach
                    </desc>
                    @for($tick = 0; $tick <= 4; $tick++)
                        <line x1="100" y1="{{ 250 - $tick * 55 }}" x2="920" y2="{{ 250 - $tick * 55 }}" stroke="#e2e8f0" />
                        <text x="88" y="{{ 254 - $tick * 55 }}" text-anchor="end" fill="#64748b" font-size="12">${{ number_format($chartMax * $tick / 4, 2) }}</text>
                    @endfor
                    @foreach($chart as $point)
                        @php
                            $height = $point['total'] / $chartMax * 220;
                            $barWidth = min(54, $chartStep * 0.65);
                            $center = 100 + ($loop->index + 0.5) * $chartStep;
                        @endphp
                        <rect x="{{ $center - $barWidth / 2 }}" y="{{ 250 - $height }}" width="{{ $barWidth }}" height="{{ $height }}" rx="4" fill="var(--layout-primary, #059669)" tabindex="0" aria-label="{{ $point['period'] }}: ${{ number_format($point['total'], 2) }}, {{ $point['count'] }} ventas">
                            <title>{{ $point['period'] }} · ${{ number_format($point['total'], 2) }} · {{ $point['count'] }} ventas</title>
                        </rect>
                        @if($loop->index % $labelEvery === 0 || $loop->last)
                            <text x="{{ $center }}" y="278" text-anchor="middle" fill="#64748b" font-size="12">{{ $point['label'] }}</text>
                        @endif
                    @endforeach
                    <text x="510" y="303" text-anchor="middle" fill="#64748b" font-size="12">Fecha (día/mes)</text>
                </svg>
            </div>
            <p class="text-xs text-slate-500 mt-2">Pasa el cursor sobre una barra para ver su total y cantidad de ventas.</p>
        @else
            <p class="empty">No hay ventas vigentes para graficar en este período.</p>
        @endif
    </section>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>
                        Factura
                    </th>
                    <th>
                        Fecha
                    </th>
                    <th>
                        Vendedor
                    </th>
                    <th>
                        Estado
                    </th>
                    <th>
                        Total
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>
                            <a class="link" href="{{ route('sales.show',$sale) }}">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td>
                            {{ $sale->sold_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            {{ $sale->user?->name??'Sin registro' }}
                        </td>
                        <td>
                            {{ $sale->status==='cancelled'?'Anulada':'Vigente' }}
                        </td>
                        <td>
                            $
                            {{ number_format($sale->total,2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">
                            Sin ventas en este período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
@endsection
