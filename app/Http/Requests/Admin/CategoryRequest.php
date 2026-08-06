<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi beneran udah dihandle middleware 'admin' di routes/web.php -
        // ini cuma lapisan kedua (defense in depth), true doang karena middleware
        // yang jadi gatekeeper utama.
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // is_active dari checkbox HTML: kalau gak dicentang, field-nya
        // GAK DIKIRIM SAMA SEKALI oleh browser (bukan false) - default-in
        // eksplisit di sini, jangan andelin default kolom DB doang.
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
