<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/', Rule::unique('service_categories', 'code')->ignore($category)],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'fee_min' => ['required', 'integer', 'min:0', 'max:1000000'],
            'fee_default' => ['required', 'integer', 'gte:fee_min', 'lte:fee_max'],
            'fee_max' => ['required', 'integer', 'gte:fee_min', 'max:1000000'],
            'requires_document' => ['nullable', 'boolean'],
            'has_item_cost' => ['nullable', 'boolean'],
            'icon' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode',
            'name' => 'nama kategori',
            'fee_min' => 'tarif minimum',
            'fee_default' => 'tarif default',
            'fee_max' => 'tarif maksimum',
            'icon' => 'ikon',
        ];
    }
}
