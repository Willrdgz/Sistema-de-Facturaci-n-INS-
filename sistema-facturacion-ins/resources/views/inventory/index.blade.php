@extends('layouts.app')
@section('heading','Movimientos de inventario')
@section('content')
    <form class="card grid gap-4 md:grid-cols-3 mb-6" method="POST" action="{{ route('inventory.store') }}">
        @csrf
        <label>
            Producto
            <select class="input" name="product_id" required>
                <option value="">
                    Seleccionar producto
                </option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id')==$product->
                        id)>
                        {{ $product->code }}
                        ·
                        {{ $product->name }}
                        (
                        {{ $product->stock }}
                        )
                    </option>
                @endforeach
            </select>
        </label>
        <label>
            Movimiento
            <select class="input" name="type">
                <option value="in" @selected(old('type')==='in')>
                    Entrada
                </option>
                <option value="out" @selected(old('type')==='out')>
                    Salida
                </option>
            </select>
        </label>
        <label>
            Cantidad
            <input class="input" type="number" name="quantity" min="1" required value="{{ old('quantity') }}">
        </label>
        <label class="md:col-span-2">
            Motivo
            <input class="input" name="reason" required maxlength="255" placeholder="Compra, reposición, daño..." value="{{ old('reason') }}">
        </label>
        <button class="btn-primary self-end">
            Registrar movimiento
        </button>
    </form>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>
                        Fecha
                    </th>
                    <th>
                        Producto
                    </th>
                    <th>
                        Tipo
                    </th>
                    <th>
                        Cantidad
                    </th>
                    <th>
                        Saldo
                    </th>
                    <th>
                        Responsable
                    </th>
                    <th>
                        Motivo
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>
                            {{ $movement->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            {{ $movement->product->name }}
                        </td>
                        <td>
                            {{ ['in'=>'Entrada','out'=>'Salida','opening'=>'Inicial','sale'=>'Venta','return'=>'Devolución'][$movement->type] }}
                        </td>
                        <td>
                            {{ $movement->quantity }}
                        </td>
                        <td>
                            {{ $movement->balance }}
                        </td>
                        <td>
                            {{ $movement->user?->name??'Sistema' }}
                        </td>
                        <td>
                            {{ $movement->reason }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty">
                            Todavía no hay movimientos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $movements->links() }}
@endsection
