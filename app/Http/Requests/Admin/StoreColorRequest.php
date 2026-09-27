<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'hex_code' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'status'   => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Vui lòng nhập tên màu.',
            'hex_code.required' => 'Vui lòng nhập mã màu HEX.',
            'hex_code.regex'    => 'Mã màu phải đúng định dạng HEX (VD: #FFFFFF hoặc #FFF).',
            'status.required'   => 'Vui lòng chọn trạng thái.',
        ];
    }
}
