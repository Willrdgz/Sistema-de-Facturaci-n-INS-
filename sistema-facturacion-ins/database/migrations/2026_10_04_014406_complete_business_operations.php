<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('seller');
            $table->boolean('active')->default(true);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('completed');
            $table->text('cancellation_reason')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(13);
            $table->json('business_snapshot')->nullable();
            $table->json('customer_snapshot')->nullable();
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance');
            $table->string('reason');
            $table->timestamps();
        });
        foreach (DB::table('products')->get() as $product) {
            DB::table('stock_movements')->insert(['product_id' => $product->id, 'type' => 'opening', 'quantity' => $product->stock, 'balance' => $product->stock, 'reason' => 'Existencias iniciales al habilitar historial', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['status', 'cancellation_reason', 'tax_rate', 'business_snapshot', 'customer_snapshot']);
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'active']));
    }
};
