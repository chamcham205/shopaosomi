<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isAdmin(); }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;
        return [
            'name'        => ['required', 'string', 'max:191'],
            'slug'        => ['nullable', 'string', 'max:191', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'      => ['required', 'in:active,inactive'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Vui lòng nhập tên danh mục.',
            'slug.unique'     => 'Slug này đã tồn tại.',
            'image.image'     => 'File phải là hình ảnh.',
            'image.mimes'     => 'Ảnh phải là định dạng jpg, jpeg, png hoặc webp.',
            'image.max'       => 'Ảnh không được vượt quá 2MB.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in'       => 'Trạng thái không hợp lệ.',
        ];
    }
}
