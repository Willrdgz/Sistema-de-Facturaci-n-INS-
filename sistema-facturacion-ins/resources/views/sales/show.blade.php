@extends('layouts.app')
@section('heading','Factura '.$sale->invoice_number)
@section('content')
    <div class="mb-4 flex justify-end gap-2 print:hidden">
        <a class="btn-secondary" href="{{ route('sales.index') }}">
            Volver
        </a>
        <button class="btn-primary" onclick="window.print()">
            Imprimir factura
        </button>
    </div>
    @if($sale->status==='cancelled')
        <div class="alert-error">
            VENTA ANULADA:
            {{ $sale->cancellation_reason }}
        </div>
    @endif
    @if(auth()->user()->role==='admin' && $sale->status==='completed')
        <form class="card mb-5 print:hidden flex gap-3" method="POST" action="{{ route('sales.cancel',$sale) }}">
            @csrf
            <input class="input" name="reason" required maxlength="255" placeholder="Motivo de anulación">
            <button class="btn-secondary">
                Anular y devolver inventario
            </button>
        </form>
    @endif
    <article class="mx-auto max-w-4xl bg-white p-8 shadow-sm print:shadow-none">
        <header class="flex justify-between border-b pb-6">
            <div>
                <div class="mb-2 inline-block rounded-lg bg-slate-950 px-3 py-2 font-black text-white">
                    INS
                </div>
                <h2 class="text-2xl font-black">
                    {{ $business?->name ?? 'INS Facturación' }}
                </h2>
                <p class="text-sm text-slate-500">
                    {{ $business?->address }}
                </p>
                <p class="text-sm text-slate-500">
                    {{ $business?->phone }}
                    ·
                    {{ $business?->email }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase tracking-widest text-slate-400">
                    Factura
                </p>
                <p class="font-mono font-bold">
                    {{ $sale->invoice_number }}
                </p>
                <p class="mt-2 text-sm">
                    {{ $sale->sold_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </header>
        <div class="my-6 grid gap-4 sm:grid-cols-2">
            <div>
                <small class="text-slate-400">
                    CLIENTE
                </small>
                <p class="font-bold">
                    {{ $sale->customer_snapshot['name'] ?? $sale->customer?->name ?? 'Consumidor final' }}
                </p>
                <p>
                    {{ $sale->customer_snapshot['document'] ?? $sale->customer?->document }}
                </p>
            </div>
            <div class="sm:text-right">
                <small class="text-slate-400">
                    FORMA DE PAGO
                </small>
                <p class="font-bold">
                    {{ ['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'][$sale->payment_method] }}
                </p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>
                            Producto
                        </th>
                        <th class="text-right">
                            Precio
                        </th>
                        <th class="text-center">
                            Cantidad
                        </th>
                        <th class="text-right">
                            Importe
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>
                                {{ $item->product_name }}
                            </td>
                            <td class="text-right">
                                $
                                {{ number_format($item->unit_price,2) }}
                            </td>
                            <td class="text-center">
                                {{ $item->quantity }}
                            </td>
                            <td class="text-right font-bold">
                                $
                                {{ number_format($item->line_total,2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ml-auto mt-6 max-w-sm">
            <div class="summary-row">
                <span>
                    Subtotal
                </span>
                <span>
                    $
                    {{ number_format($sale->subtotal,2) }}
                </span>
            </div>
            <div class="summary-row">
                <span>
                    Descuento
                </span>
                <span>
                    -$
                    {{ number_format($sale->discount,2) }}
                </span>
            </div>
            <div class="summary-row">
                <span>
                    IVA
                </span>
                <span>
                    $
                    {{ number_format($sale->tax,2) }}
                </span>
            </div>
            <div class="mt-3 flex justify-between border-t pt-3 text-xl font-black">
                <span>
                    Total
                </span>
                <span>
                    $
                    {{ number_format($sale->total,2) }}
                </span>
            </div>
        </div>
        <footer class="mt-12 border-t pt-5 text-center text-sm text-slate-400">
            {{ $sale->notes }}
            <br>
            Atendido por:
            {{ $sale->user?->name ?? 'Sin registro' }}
            <br>
            Gracias por su compra.
        </footer>
    </article>
@endsection
