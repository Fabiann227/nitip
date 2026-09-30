<?php

namespace App\Http\Requests\Admin;

use App\Enums\DisputeResolution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resolve', $this->route('dispute')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'resolution' => ['required', Rule::in(DisputeResolution::values())],
            'note' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['resolution' => 'keputusan', 'note' => 'catatan resolusi'];
    }
}
