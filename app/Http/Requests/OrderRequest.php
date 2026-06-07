<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return auth()->check();
         return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string|in:cash_on_delivery,card,bank_transfer',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom message for validation errors.
     */

    public function messages():array
    {
        return [
            'items.required' => 'At least one product is required.',
            'items.min' => 'At least one product is required.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.product_id.exists' => 'One or more products do not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     */

    public function attributes(): array
    {
        return [
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'quantity',
        ];
    }
}
