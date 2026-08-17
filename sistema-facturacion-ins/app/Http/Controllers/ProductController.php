<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')->when(request('search'), fn ($query, $search) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))->orderBy('name')->paginate(10)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', ['categories' => Category::where('active', true)->orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated() + ['active' => $request->boolean('active')]);

        return to_route('products.index')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product, 'categories' => Category::where('active', true)->orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated() + ['active' => $request->boolean('active')]);

        return to_route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['active' => false]);

        return back()->with('success', 'Producto desactivado.');
    }
}
