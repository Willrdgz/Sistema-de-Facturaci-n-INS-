<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $query = Sale::with('customer', 'user')->when($filters['search'] ?? null, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%"))->when($filters['from'] ?? null, fn ($q, $s) => $q->whereDate('sold_at', '>=', $s))->when($filters['to'] ?? null, fn ($q, $s) => $q->whereDate('sold_at', '<=', $s));

        return view('sales.index', ['sales' => $query->latest('sold_at')->paginate(10)->withQueryString()]);
    }

    public function create(): View
    {
        return view('sales.create', ['customers' => Customer::where('active', true)->orderBy('name')->get(), 'products' => Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get(), 'taxRate' => Business::value('tax_rate') ?? 13]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $sale = DB::transaction(function () use ($request) {
            $items = collect($request->validated('items'))->sortBy('product_id');
            $products = Product::whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            $prepared = [];
            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                if (! $product || ! $product->active || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => 'Producto no disponible o existencia insuficiente.']);
                }
                $price = (int) round((float) $product->price * 100);
                $line = $price * $item['quantity'];
                $subtotal += $line;
                if ($subtotal > 99999999999) {
                    throw ValidationException::withMessages(['items' => 'La venta excede el importe permitido.']);
                }
                $prepared[] = ['product' => $product, 'quantity' => $item['quantity'], 'line' => $line];
            }
            $discount = (int) round((float) ($request->validated('discount') ?? 0) * 100);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount' => 'El descuento no puede superar el subtotal.']);
            }
            $customer = Customer::find($request->validated('customer_id'));
            if ($customer && ! $customer->active) {
                throw ValidationException::withMessages(['customer_id' => 'Cliente inactivo.']);
            }
            $business = Business::first();
            $rate = (float) ($business?->tax_rate ?? 13);
            $tax = (int) round(($subtotal - $discount) * $rate / 100);
            $sale = Sale::create(['user_id' => $request->user()->id, 'customer_id' => $customer?->id, 'invoice_number' => 'FAC-'.strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 20)), 'sold_at' => now(), 'payment_method' => $request->validated('payment_method'), 'subtotal' => $subtotal / 100, 'discount' => $discount / 100, 'tax' => $tax / 100, 'tax_rate' => $rate, 'total' => ($subtotal - $discount + $tax) / 100, 'notes' => $request->validated('notes'), 'business_snapshot' => $business?->only(['name', 'address', 'phone', 'email', 'tax_id']), 'customer_snapshot' => $customer?->only(['name', 'document', 'address']) ?? ['name' => 'Consumidor final']]);
            foreach ($prepared as $item) {
                $product = $item['product'];
                $sale->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'unit_price' => $product->price, 'quantity' => $item['quantity'], 'line_total' => $item['line'] / 100]);
                $product->decrement('stock', $item['quantity']);
                StockMovement::create(['product_id' => $product->id, 'user_id' => $request->user()->id, 'sale_id' => $sale->id, 'type' => 'sale', 'quantity' => $item['quantity'], 'balance' => $product->stock, 'reason' => $sale->invoice_number]);
            }

            return $sale;
        });

        return to_route('sales.show', $sale)->with('success', 'Venta registrada y existencias actualizadas.');
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', ['sale' => $sale->load('customer', 'items', 'user'), 'business' => $sale->business_snapshot ? new Business($sale->business_snapshot) : Business::first()]);
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);
        DB::transaction(function () use ($sale, $data, $request) {
            $locked = Sale::lockForUpdate()->findOrFail($sale->id);
            if ($locked->status === 'cancelled') {
                throw ValidationException::withMessages(['reason' => 'Esta venta ya fue anulada.']);
            }
            foreach ($locked->items()->orderBy('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
                StockMovement::create(['product_id' => $product->id, 'user_id' => $request->user()->id, 'sale_id' => $locked->id, 'type' => 'return', 'quantity' => $item->quantity, 'balance' => $product->stock, 'reason' => 'Anulación: '.$data['reason']]);
            }
            $locked->update(['status' => 'cancelled', 'cancellation_reason' => $data['reason']]);
        });

        return back()->with('success','Venta anulada y productos devueltos al inventario.');
    }
}
