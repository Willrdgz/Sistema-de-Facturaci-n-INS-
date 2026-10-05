<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('inventory.index', ['products' => Product::where('active', true)->orderBy('name')->get(), 'movements' => StockMovement::with('product', 'user')->latest('id')->paginate(20)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'type' => 'required|in:in,out', 'quantity' => 'required|integer|min:1|max:1000000', 'reason' => 'required|string|max:255']);
        DB::transaction(function () use ($data, $request) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            abort_unless($product->active, 422, 'Producto desactivado.');
            $balance = $product->stock + ($data['type'] === 'in' ? $data['quantity'] : -$data['quantity']);
            if ($balance < 0) {
                throw ValidationException::withMessages(['quantity' => 'La salida supera las existencias disponibles.']);
            }
            if ($balance > 2147483647) {
                throw ValidationException::withMessages(['quantity' => 'La entrada supera el límite de existencias permitido.']);
            }
            $product->update(['stock' => $balance]);
            StockMovement::create($data + ['user_id' => $request->user()->id, 'balance' => $balance]);
        });

        return back()->with('success', 'Movimiento registrado y existencias actualizadas.');
    }
}
