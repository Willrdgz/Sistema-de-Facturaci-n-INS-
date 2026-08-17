<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('sales.index', ['sales' => Sale::with('customer')->latest('sold_at')->paginate(10)]);
    }

    public function create(): View
    {
        return view('sales.create', ['customers' => Customer::where('active', true)->orderBy('name')->get(), 'products' => Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get(), 'taxRate' => Business::value('tax_rate') ?? 13]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $sale = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $preparedItems = [];
            foreach ($request->validated('items') as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => "Existencia insuficiente para {$product->name}."]);
                }
                $lineTotal = round((float) $product->price * $item['quantity'], 2);
                $subtotal += $lineTotal;
                $preparedItems[] = ['product' => $product, 'quantity' => $item['quantity'], 'line_total' => $lineTotal];
            }
            $discount = min((float) ($request->validated('discount') ?? 0), $subtotal);
            $taxRate = (float) (Business::value('tax_rate') ?? 13);
            $tax = round(($subtotal - $discount) * ($taxRate / 100), 2);
            $sale = Sale::create(['customer_id' => $request->validated('customer_id'), 'invoice_number' => 'FAC-'.now()->format('Ymd-His').'-'.random_int(10, 99), 'sold_at' => now(), 'payment_method' => $request->validated('payment_method'), 'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $subtotal - $discount + $tax, 'notes' => $request->validated('notes')]);
            foreach ($preparedItems as $item) {
                $sale->items()->create(['product_id' => $item['product']->id, 'product_name' => $item['product']->name, 'unit_price' => $item['product']->price, 'quantity' => $item['quantity'], 'line_total' => $item['line_total']]);
                $item['product']->decrement('stock', $item['quantity']);
            }

            return $sale;
        });

        return to_route('sales.show', $sale)->with('success', 'Venta registrada y existencias actualizadas.');
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', ['sale' => $sale->load('customer', 'items'), 'business' => Business::first()]);
    }
}
