<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\CarbonImmutable;
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
        $start = CarbonImmutable::parse($from)->startOfDay();
        $end = CarbonImmutable::parse($to)->startOfDay();
        $days = max(1, (int) $start->diffInDays($end) + 1);
        $bucketDays = (int) ceil($days / 31);
        $chart = [];
        for ($offset = 0; $offset < $days; $offset += $bucketDays) {
            $bucketStart = $start->addDays($offset);
            $bucketEnd = $bucketStart->addDays($bucketDays - 1)->min($end);
            $chart[] = [
                'label' => $bucketStart->format('d/m'),
                'period' => $bucketStart->format('d/m/Y').($bucketDays > 1 ? ' — '.$bucketEnd->format('d/m/Y') : ''),
                'total' => 0,
                'count' => 0,
            ];
        }
        $dailySales = (clone $query)->reorder()->where('status', 'completed')
            ->selectRaw('DATE(sold_at) as day, SUM(total) as amount, COUNT(*) as operations')
            ->groupByRaw('DATE(sold_at)')->get();
        foreach ($dailySales as $day) {
            $index = (int) floor($start->diffInDays(CarbonImmutable::parse($day->day)) / $bucketDays);
            $chart[$index]['total'] += (int) round((float) $day->amount * 100);
            $chart[$index]['count'] += (int) $day->operations;
        }
        foreach ($chart as &$bucket) {
            $bucket['total'] /= 100;
        }
        unset($bucket);

        return view('reports.index', ['sales' => $query->paginate(20)->withQueryString(), 'revenue' => $revenue, 'from' => $from, 'to' => $to, 'chart' => $chart, 'bucketDays' => $bucketDays, 'stockValue' => Product::where('active', true)->selectRaw('SUM(stock * cost) as value')->value('value') ?? 0]);
    }
}
