<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a sale creates an invoice and reduces product stock', function () {
    Business::create(['name' => 'Negocio de prueba', 'tax_rate' => 13]);
    $category = Category::create(['name' => 'Herramientas']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'TEST-01', 'name' => 'Martillo', 'cost' => 5, 'price' => 10, 'stock' => 8, 'minimum_stock' => 2]);

    $response = $this->post(route('sales.store'), [
        'payment_method' => 'cash',
        'discount' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ]);

    $sale = Sale::first();
    $response->assertRedirect(route('sales.show', $sale));
    expect($sale->subtotal)->toBe('20.00')->and($sale->tax)->toBe('2.60')->and($sale->total)->toBe('22.60');
    expect($product->fresh()->stock)->toBe(6);
    $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => 2]);
});

test('a sale cannot exceed available stock', function () {
    $category = Category::create(['name' => 'Materiales']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'TEST-02', 'name' => 'Cemento', 'cost' => 7, 'price' => 9, 'stock' => 1, 'minimum_stock' => 1]);

    $this->from(route('sales.create'))->post(route('sales.store'), ['payment_method' => 'cash', 'items' => [['product_id' => $product->id, 'quantity' => 2]]])->assertRedirect(route('sales.create'))->assertSessionHasErrors('items');

    expect($product->fresh()->stock)->toBe(1);
    $this->assertDatabaseCount('sales', 0);
});
