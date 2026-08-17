<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['customer_id' => ['nullable', 'exists:customers,id'], 'payment_method' => ['required', 'in:cash,card,transfer'], 'discount' => ['nullable', 'numeric', 'min:0'], 'notes' => ['nullable', 'string', 'max:500'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'distinct', 'exists:products,id'], 'items.*.quantity' => ['required', 'integer', 'min:1']];
    }
}
