<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $data = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'format' => 'nullable|in:csv']);
        $from = $data['from'] ?? now()->startOfMonth()->toDateString();
        $to = $data['to'] ?? today()->toDateString();
        $query = Sale::with('customer', 'user')->whereBetween('sold_at', [$from.' 00:00:00', $to.' 23:59:59'])->latest('sold_at');
        if (($data['format'] ?? null) === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Factura', 'Fecha', 'Cliente', 'Vendedor', 'Estado', 'Subtotal', 'Descuento', 'Impuesto', 'Total']);
                foreach ($query->cursor() as $sale) {
                    $values = [$sale->invoice_number, $sale->sold_at->format('d/m/Y H:i'), $sale->customer?->name ?? 'Consumidor final', $sale->user?->name ?? 'Sin registro', $sale->status, $sale->subtotal, $sale->discount, $sale->tax, $sale->total];
                    $values = array_map(fn ($v) => preg_match('/^[=+@-]/', (string) $v) ? "'".$v : $v, $values);
                    fputcsv($stream, $values);
                }fclose($stream);
            }, 'ventas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $revenue = (clone $query)->where('status', 'completed')->sum('total');

        return view('reports.index', ['sales' => $query->paginate(20)->withQueryString(), 'revenue' => $revenue, 'from' => $from, 'to' => $to, 'stockValue' => Product::where('active', true)->selectRaw('SUM(stock * cost) as value')->value('value') ?? 0]);
    }
}
