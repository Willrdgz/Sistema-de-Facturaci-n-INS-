<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected $fillable = ['customer_id', 'user_id', 'invoice_number', 'sold_at', 'payment_method', 'subtotal', 'discount', 'tax', 'total', 'notes', 'status', 'cancellation_reason', 'tax_rate', 'business_snapshot', 'customer_snapshot'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    protected function casts(): array
    {
        return ['sold_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2', 'business_snapshot' => 'array', 'customer_snapshot' => 'array'];
    }
}
