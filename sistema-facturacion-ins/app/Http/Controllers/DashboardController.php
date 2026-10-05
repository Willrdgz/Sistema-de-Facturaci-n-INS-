<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'salesToday' => Sale::where('status', 'completed')->whereDate('sold_at', today())->sum('total'),
            'salesCount' => Sale::where('status', 'completed')->whereDate('sold_at', today())->count(),
            'customerCount' => Customer::where('active', true)->count(),
            'productCount' => Product::where('active', true)->count(),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'recentSales' => Sale::with('customer')->latest('sold_at')->limit(5)->get(),
            'lowStockProducts' => Product::with('category')->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
