<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            // max:2048 KB (2MB) - batas wajar buat thumbnail produk, jangan
            // biarin upload tak terbatas (resiko habisin storage Cloudinary).
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.ukuran' => ['nullable', 'string', 'max:100'],
            'variants.*.bahan' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.min_order' => ['required', 'integer', 'min:1'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*._delete' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);

        $variants = collect($this->input('variants', []))->map(function ($variant) {
            $variant['is_active'] = ! empty($variant['is_active']);
            $variant['_delete'] = ! empty($variant['_delete']);
            return $variant;
        })->all();

        $this->merge(['variants' => $variants]);
    }
}
