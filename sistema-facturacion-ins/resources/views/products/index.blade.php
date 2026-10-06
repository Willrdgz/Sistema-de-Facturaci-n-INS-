@extends('layouts.app')
@section('heading','Productos e inventario')
@section('content')
    <div class="toolbar">
        <form class="flex gap-2">
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Código o producto">
            <button class="btn-secondary">
                Buscar
            </button>
        </form>
        @if(auth()->user()->role==='admin')
            <a class="btn-primary" href="{{ route('products.create') }}">
                + Nuevo producto
            </a>
        @endif
    </div>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>
                        Código
                    </th>
                    <th>
                        Producto
                    </th>
                    <th>
                        Categoría
                    </th>
                    <th class="text-right">
                        Precio
                    </th>
                    <th class="text-center">
                        Existencia
                    </th>
                    <th>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="font-mono text-xs">
                            {{ $product->code }}
                        </td>
                        <td>
                            <strong>
                                {{ $product->name }}
                            </strong>
                            <small class="block text-slate-400">
                                {{ $product->active?'Activo':'Inactivo' }}
                            </small>
                        </td>
                        <td>
                            {{ $product->category->name }}
                        </td>
                        <td class="text-right font-bold">
                            $
                            {{ number_format($product->price,2) }}
                        </td>
                        <td class="text-center">
                            <span class="{{ $product->stock <= $product->minimum_stock?'badge-warning':'badge-success' }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if(auth()->user()->role==='admin')
                                <a class="link" href="{{ route('products.edit',$product) }}">
                                    Editar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            No hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
