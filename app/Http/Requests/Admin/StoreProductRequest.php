<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'category_id'       => ['required', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:191'],
            'slug'              => ['nullable', 'string', 'max:191', 'unique:products,slug'],
            'sku'               => ['required', 'string', 'max:100', 'unique:products,sku'],
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
            // Ảnh
            'images'            => ['nullable', 'array', 'max:10'],
            'images.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'], // 3MB
            // Variants
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
        return [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists'   => 'Danh mục không tồn tại.',
            'name.required'        => 'Vui lòng nhập tên sản phẩm.',
            'sku.required'         => 'Vui lòng nhập mã SKU.',
            'sku.unique'           => 'SKU này đã tồn tại. Vui lòng nhập SKU khác.',
            'slug.unique'          => 'Slug này đã tồn tại.',
            'price.required'       => 'Vui lòng nhập giá.',
            'price.min'            => 'Giá không được âm.',
            'sale_price.lt'        => 'Giá sale phải nhỏ hơn giá gốc.',
            'sale_end.after_or_equal' => 'Ngày kết thúc sale phải sau ngày bắt đầu.',
            'status.required'      => 'Vui lòng chọn trạng thái.',
            'status.in'            => 'Trạng thái không hợp lệ.',
            'images.*.image'       => 'File phải là ảnh hợp lệ.',
            'images.*.mimes'       => 'Ảnh phải là định dạng jpg, jpeg, png hoặc webp.',
            'images.*.max'         => 'Ảnh không được vượt quá 3MB.',
            'stock.*.min'          => 'Tồn kho không được âm.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-generate slug nếu chưa nhập
        if (empty($this->slug) && ! empty($this->name)) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
        $this->merge([
            'featured' => $this->boolean('featured'),
        ]);
    }
}
