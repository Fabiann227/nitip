<?php

namespace App\Http\Requests\Orders;

use App\Enums\DisputeReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dispute', $this->route('order')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uploads = config('nitip.uploads');

        return [
            'reason' => ['required', Rule::in(DisputeReason::values())],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'evidence' => ['nullable', 'file', 'image', 'mimes:'.implode(',', $uploads['image_mimes']), 'max:'.$uploads['image_max_kb']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['reason' => 'jenis masalah', 'description' => 'kronologi', 'evidence' => 'bukti'];
    }
}
