<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        return ['category_id' => ['required', 'exists:categories,id'], 'code' => ['required', 'string', 'max:30', 'unique:products,code,'.$this->route('product')?->id], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:500'], 'cost' => ['required', 'numeric', 'min:0'], 'price' => ['required', 'numeric', 'min:0'], 'stock' => ['required', 'integer', 'min:0'], 'minimum_stock' => ['required', 'integer', 'min:0'], 'active' => ['nullable', 'boolean']];
    }
}
