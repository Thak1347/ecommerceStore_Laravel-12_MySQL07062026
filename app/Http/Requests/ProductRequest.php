<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return auth()->user() && auth()->user()->isAdmin();
        $user = $this->user();
        return $user && $user->isAdmin();

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        return [

            'category_id' => 'required|exists:categories,id',
            'sku' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($productId)],
            'name'=> 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'description'=>'nullable|string',
            'image'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price'=>'required|numeric|min:0',
            'cost_price'=>'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'active'=>'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required'=> 'Please select a category for the product.',
            'category_id.exists'=> 'The selected category does not exist.',
            'sku.required'=> 'The SKU is required.',
            'sku.unique'=> 'The SKU is already in use.',
            'name.required'=> 'The product name is required.',
            'slug.required'=> 'The slug is required.',
            'slug.unique'=> 'This slug is already taken.',
            'price.required'=>'The price is required.',
            'price.numeric'=>'The price must be a number.',
            'price.min'=> 'The price must be at least 0.',
            'stock_qty.required'=> 'The stock quantity is required.',
            'stock_qty.integer'=>'The stock quantity must be an integer.',
            'stock_qty.min'=>'The stock quantity must be at least 0.',
            'image.image'=>'The file must be an image.',
            'image.mimes'=>'The image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max'=>'The image size must not exceed 2MB.',

        ];

    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        if($this->has('active')){
            $this->merge([
                'active' => filter_var($this->active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
