<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
        $categoryId = $this->route('category');

        return [
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($categoryId)],
            'description'=> 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'active' => 'boolean',
        ];
        
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'=> 'The category name is required.',
            'name.max'=>'The category name must not exceed 255 characters.',
            'slug.required'=>'the slug is required.',
            'slug.unique'=> 'The slug must be unique.',
            'image.image'=>'The file must be an image.',
            'image.mimes'=> 'The image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max'=> 'the image size must not exceed 2MB.',

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
