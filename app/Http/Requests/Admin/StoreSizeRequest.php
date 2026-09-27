<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSizeRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isAdmin(); }

    public function rules(): array
    {
        $sizeId = $this->route('size')?->id;
        return [
            'name'       => ['required', 'string', 'max:20', Rule::unique('sizes', 'name')->ignore($sizeId)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status'     => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên kích cỡ.',
            'name.unique'   => 'Kích cỡ này đã tồn tại.',
        ];
    }
}
