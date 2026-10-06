<?php

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('chart includes all filtered completed sales and days without sales', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    for ($i = 0; $i < 21; $i++) {
        Sale::create(['invoice_number' => 'CHART-'.$i, 'sold_at' => '2026-10-03 12:00:00', 'payment_method' => 'cash', 'subtotal' => 1.25, 'tax' => 0, 'total' => 1.25, 'status' => 'completed']);
    }
    foreach (['cancelled' => '2026-10-04', 'completed' => '2026-09-30'] as $status => $date) {
        Sale::create(['invoice_number' => 'OTHER-'.$status, 'sold_at' => $date.' 12:00:00', 'payment_method' => 'cash', 'subtotal' => 100, 'tax' => 0, 'total' => 100, 'status' => $status]);
    }
    $response = $this->get(route('reports.index', ['from' => '2026-10-01', 'to' => '2026-10-05', 'page' => 2]));
    $response->assertOk()->assertViewHas('chart', function ($chart) {
        return count($chart) === 5 && $chart[0]['total'] == 0
            && $chart[2]['total'] == 26.25 && $chart[2]['count'] === 21
            && $chart[3]['total'] == 0 && array_sum(array_column($chart, 'total')) == 26.25;
    })->assertSee('Evolución de las ventas');
});

test('long ranges are grouped into at most thirty one chart bars', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    Sale::create(['invoice_number' => 'YEAR-END', 'sold_at' => '2026-12-31 23:59:59', 'payment_method' => 'cash', 'subtotal' => 25, 'tax' => 0, 'total' => 25, 'status' => 'completed']);
    $this->get(route('reports.index', ['from' => '2026-01-01', 'to' => '2026-12-31']))
        ->assertOk()->assertViewHas('chart', fn ($chart) => count($chart) <= 31 && array_sum(array_column($chart, 'total')) == 25);
});

test('empty periods show an explanatory message', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('reports.index', ['from' => '2026-10-05', 'to' => '2026-10-05']))
        ->assertOk()->assertSee('No hay ventas vigentes para graficar en este período.');
});
