@extends('layouts.app')
@section('heading','Registrar venta')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}" id="sale-form">
        @csrf
        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <section class="card">
                    <h2 class="mb-4 text-lg font-bold">
                        Información de la venta
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label>
                            Cliente
                            <select class="input" name="customer_id">
                                <option value="">
                                    Consumidor final
                                </option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id')==$customer->
                                        id)>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Forma de pago
                            <select class="input" name="payment_method">
                                <option value="cash">
                                    Efectivo
                                </option>
                                <option value="card" @selected(old('payment_method')==='card')>
                                    Tarjeta
                                </option>
                                <option value="transfer" @selected(old('payment_method')==='transfer')>
                                    Transferencia
                                </option>
                            </select>
                        </label>
                    </div>
                </section>
                <section class="card">
                    <div class="section-heading">
                        <h2>
                            Productos
                        </h2>
                        <button type="button" id="add-item" class="btn-secondary">
                            + Agregar producto
                        </button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>
                                        Producto
                                    </th>
                                    <th class="w-28">
                                        Cantidad
                                    </th>
                                    <th class="text-right">
                                        Precio
                                    </th>
                                    <th class="text-right">
                                        Importe
                                    </th>
                                    <th>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="items">
                            </tbody>
                        </table>
                    </div>
                    <p id="empty-items" class="empty">
                        Agrega al menos un producto.
                    </p>
                </section>
            </div>
            <aside class="card h-fit">
                <h2 class="mb-4 text-lg font-bold">
                    Resumen
                </h2>
                <div class="summary-row">
                    <span>
                        Subtotal
                    </span>
                    <strong id="subtotal">
                        $0.00
                    </strong>
                </div>
                <label class="my-4 block">
                    Descuento
                    <input id="discount" class="input" type="number" name="discount" min="0" step="0.01" value="{{ old('discount',0) }}">
                </label>
                <div class="summary-row">
                    <span>
                        IVA (
                        {{ number_format($taxRate,2) }}
                        %)
                    </span>
                    <strong id="tax">
                        $0.00
                    </strong>
                </div>
                <div class="mt-4 flex justify-between border-t pt-4 text-xl">
                    <strong>
                        Total
                    </strong>
                    <strong class="text-emerald-600" id="total">
                        $0.00
                    </strong>
                </div>
                <label class="mt-5 block">
                    Notas
                    <textarea class="input" name="notes" placeholder="Observaciones opcionales">{{ old('notes') }}</textarea>
                </label>
                <button class="btn-primary mt-5 w-full">
                    Guardar y generar factura
                </button>
            </aside>
        </div>
    </form>
    <template id="item-template">
        <tr>
            <td>
                <select class="input product-select" name="items[INDEX][product_id]" required>
                    <option value="">
                        Seleccione un producto
                    </option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                            {{ $product->code }}
                            ·
                            {{ $product->name }}
                            (
                            {{ $product->stock }}
                            disp.)
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input class="input quantity" type="number" name="items[INDEX][quantity]" min="1" value="1" required>
            </td>
            <td class="price text-right">
                $0.00
            </td>
            <td class="line-total text-right font-bold">
                $0.00
            </td>
            <td>
                <button type="button" class="remove text-red-500">
                    ×
                </button>
            </td>
        </tr>
    </template>
    <script>document.addEventListener('DOMContentLoaded',()=>{const body=document.querySelector('#items'),tpl=document.querySelector('#item-template'),empty=document.querySelector('#empty-items'),discount=document.querySelector('#discount'),rate={{ (float)$taxRate }};let index=0;const restored=Object.values(@json(old('items',[])));function money(n){return '$'+n.toFixed(2)}function calculate(){let subtotal=0;body.querySelectorAll('tr').forEach(row=>{const option=row.querySelector('.product-select').selectedOptions[0],price=Number(option?.dataset.price||0),qty=Number(row.querySelector('.quantity').value||0),line=price*qty;row.querySelector('.price').textContent=money(price);row.querySelector('.line-total').textContent=money(line);subtotal+=line});const disc=Math.min(Number(discount.value||0),subtotal),tax=Math.round((subtotal-disc)*rate)/100;document.querySelector('#subtotal').textContent=money(subtotal);document.querySelector('#tax').textContent=money(tax);document.querySelector('#total').textContent=money(subtotal-disc+tax);empty.classList.toggle('hidden',body.children.length>0)}function add(item){body.insertAdjacentHTML('beforeend',tpl.innerHTML.replaceAll('INDEX',index++));if(item?.product_id){const row=body.lastElementChild;row.querySelector('.product-select').value=item.product_id;row.querySelector('.quantity').value=item.quantity}calculate()}document.querySelector('#add-item').addEventListener('click',()=>add());body.addEventListener('input',calculate);body.addEventListener('click',e=>{if(e.target.classList.contains('remove')){e.target.closest('tr').remove();calculate()}});discount.addEventListener('input',calculate);if(restored.length){restored.forEach(add)}else{add()}});</script>
@endsection
