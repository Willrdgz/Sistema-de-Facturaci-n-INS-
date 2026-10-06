<?php

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrator can persist layout colors and all users see them', function () {
    $business = Business::create(['name' => 'InVenta', 'tax_rate' => 13]);
    $colors = ['sidebar' => '#ffffff', 'background' => '#eeeeee', 'primary' => '#663399', 'header' => '#112233'];
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('business.update'), ['name' => 'InVenta', 'tax_rate' => 13, 'theme_colors' => $colors])
        ->assertSessionHasNoErrors();
    expect($business->fresh()->theme_colors)->toBe($colors);
    $this->actingAs(User::factory()->create(['role' => 'seller']))->get(route('customers.index'))
        ->assertOk()->assertSee('--layout-primary: #663399;', false)
        ->assertSee('--layout-sidebar-text: #020617;', false)
        ->assertSee('--logo-invert: 0;', false);
});

test('invalid theme colors cannot be saved or injected into styles', function () {
    $business = Business::create(['name' => 'Original', 'tax_rate' => 13]);
    $colors = Business::DEFAULT_COLORS;
    $colors['primary'] = '#fff; background: red';
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('business.update'), ['name' => 'Changed', 'tax_rate' => 13, 'theme_colors' => $colors])
        ->assertSessionHasErrors('theme_colors.primary');
    expect($business->fresh()->name)->toBe('Original');
    expect($business->fresh()->theme_colors)->toBeNull();
});

test('seller cannot change global layout settings', function () {
    $this->actingAs(User::factory()->create(['role' => 'seller']))
        ->put(route('business.update'), ['name' => 'InVenta', 'tax_rate' => 13, 'theme_colors' => Business::DEFAULT_COLORS])
        ->assertForbidden();
});

test('updates without colors preserve theme and original colors can be restored', function () {
    $colors = array_replace(Business::DEFAULT_COLORS, ['primary' => '#663399']);
    $business = Business::create(['name' => 'InVenta', 'tax_rate' => 13, 'theme_colors' => $colors]);
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('business.update'), ['name' => 'InVenta', 'tax_rate' => 13])->assertSessionHasNoErrors();
    expect($business->fresh()->theme_colors)->toBe($colors);
    $this->put(route('business.update'), ['name' => 'InVenta', 'tax_rate' => 13, 'theme_colors' => Business::DEFAULT_COLORS])
        ->assertSessionHasNoErrors();
    expect($business->fresh()->layoutColors())->toBe(Business::DEFAULT_COLORS);
});
