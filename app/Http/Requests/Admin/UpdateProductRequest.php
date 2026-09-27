<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isAdmin(); }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');
        return [
            'category_id'       => ['required', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:191'],
            'slug'              => ['nullable', 'string', 'max:191', Rule::unique('products', 'slug')->ignore($productId)],
            'sku'               => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'material'          => ['nullable', 'string', 'max:191'],
            'fit'               => ['nullable', 'string', 'max:100'],
            'brand'             => ['nullable', 'string', 'max:100'],
            'price'             => ['required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'sale_start'        => ['nullable', 'date'],
            'sale_end'          => ['nullable', 'date', 'after_or_equal:sale_start'],
            'featured'          => ['boolean'],
            'status'            => ['required', 'in:active,inactive,draft'],
            'images'            => ['nullable', 'array', 'max:10'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'color_ids'         => ['nullable', 'array'],
            'color_ids.*'       => ['exists:colors,id'],
            'size_ids'          => ['nullable', 'array'],
            'size_ids.*'        => ['exists:sizes,id'],
            'stock'             => ['nullable', 'array'],
            'stock.*'           => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return (new StoreProductRequest())->messages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['featured' => $this->boolean('featured')]);
    }
}
