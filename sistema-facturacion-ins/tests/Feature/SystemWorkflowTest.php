<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected and active users can login and logout', function () {
    $this->get('/')->assertRedirect(route('login'));
    $user = User::factory()->create(['password' => 'secure-password-123']);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secure-password-123'])->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});
test('inactive accounts cannot login or continue an existing session', function () {
    $user = User::factory()->create(['active' => false, 'password' => 'secure-password-123']);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secure-password-123'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->actingAs($user)->get('/')->assertRedirect(route('login'));
});
test('seller cannot manage users settings products or reports', function () {
    $this->actingAs(User::factory()->create(['role' => 'seller']));
    foreach (['users.index', 'business.edit', 'reports.index', 'products.create', 'categories.index'] as $route) {
        $this->get(route($route))->assertForbidden();
    }
    $this->post(route('products.store'), [])->assertForbidden();
});
test('administrator can load all screens and cannot deactivate last administrator', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    $this->seed();
    foreach (['dashboard', 'users.index', 'users.create', 'business.edit', 'reports.index', 'inventory.index', 'products.index', 'products.create', 'customers.index', 'customers.create', 'categories.index', 'categories.create', 'sales.index', 'sales.create'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->put(route('users.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'role' => 'seller', 'active' => 0])->assertSessionHasErrors('role');
    expect($admin->fresh()->role)->toBe('admin');
});
test('inventory entries and exits record balances and reject overselling', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::create(['name' => 'Inventario']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'INV', 'name' => 'Producto', 'price' => 10, 'stock' => 2, 'minimum_stock' => 1]);
    $this->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'in', 'quantity' => 3, 'reason' => 'Compra'])->assertSessionHasNoErrors();
    expect($product->fresh()->stock)->toBe(5);
    $this->post(route('inventory.store'), ['product_id' => $product->id, 'type' => 'out', 'quantity' => 6, 'reason' => 'Salida'])->assertSessionHasErrors('quantity');
    expect($product->fresh()->stock)->toBe(5);
    $this->assertDatabaseCount('stock_movements', 1);
});
test('sale snapshots survive edits and cancellation restores stock only once', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    Business::create(['name' => 'Negocio original', 'tax_rate' => 13]);
    $category = Category::create(['name' => 'Ventas']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'SALE', 'name' => 'Producto', 'price' => 10, 'stock' => 5, 'minimum_stock' => 1]);
    $this->post(route('sales.store'), ['payment_method' => 'cash', 'items' => [['product_id' => $product->id, 'quantity' => 2]]])->assertSessionHasNoErrors();
    $sale = Sale::first();
    expect($sale->user_id)->toBe($admin->id)->and($product->fresh()->stock)->toBe(3);
    Business::first()->update(['name' => 'Negocio nuevo']);
    $this->get(route('sales.show', $sale))->assertOk()->assertSee('Negocio original');
    $this->post(route('sales.cancel', $sale), ['reason' => 'Error'])->assertSessionHasNoErrors();
    expect($product->fresh()->stock)->toBe(5);
    $this->post(route('sales.cancel', $sale), ['reason' => 'Reintento'])->assertSessionHasErrors('reason');
    expect($product->fresh()->stock)->toBe(5);
});
test('inactive products and excessive discounts never create a sale', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::create(['name' => 'Validaciones']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'VALID', 'name' => 'Producto', 'price' => 10, 'stock' => 5, 'active' => false]);
    $payload = ['payment_method' => 'cash', 'items' => [['product_id' => $product->id, 'quantity' => 1]]];
    $this->post(route('sales.store'), $payload)->assertSessionHasErrors('items');
    $product->update(['active' => true]);
    $this->post(route('sales.store'), $payload + ['discount' => 20])->assertSessionHasErrors('discount');
    $this->assertDatabaseCount('sales', 0);
});

test('sales report excludes cancellations and exports CSV', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    Sale::create(['invoice_number' => 'FAC-REPORT', 'sold_at' => now(), 'payment_method' => 'cash', 'subtotal' => 10, 'tax' => 1.30, 'total' => 11.30, 'status' => 'completed']);
    Sale::create(['invoice_number' => 'FAC-CANCELLED', 'sold_at' => now(), 'payment_method' => 'cash', 'subtotal' => 20, 'tax' => 2.60, 'total' => 22.60, 'status' => 'cancelled']);
    $this->get(route('reports.index'))->assertOk()->assertViewHas('revenue', fn ($value) => (float) $value === 11.30);
    $response = $this->get(route('reports.index', ['format' => 'csv']));
    $response->assertDownload('ventas.csv');
    expect($response->streamedContent())->toContain('FAC-REPORT')->toContain('FAC-CANCELLED');
});

test('editing product details cannot silently overwrite inventory', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $category = Category::create(['name' => 'Edición']);
    $product = Product::create(['category_id' => $category->id, 'code' => 'EDIT', 'name' => 'Producto', 'price' => 10, 'stock' => 5]);
    $this->put(route('products.update', $product), ['category_id' => $category->id, 'code' => 'EDIT', 'name' => 'Actualizado', 'price' => 11, 'cost' => 5, 'stock' => 100, 'minimum_stock' => 1, 'active' => 1])->assertSessionHasNoErrors();
    expect($product->fresh()->stock)->toBe(5)->and($product->fresh()->name)->toBe('Actualizado');
});
