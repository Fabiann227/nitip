<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "Sudah diserahkan": hand-off confirmation by the fulfiller (receipt optional if already uploaded).
 */
class MarkDeliveredRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('markDelivered', $this->route('order')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uploads = config('nitip.uploads');
        $order = $this->route('order');
        $needsReceipt = ($order?->category?->has_item_cost ?? true) && ! $order?->hasReceipt();

        return [
            'receipt' => [$needsReceipt ? 'required' : 'nullable', 'file', 'image', 'mimes:'.implode(',', $uploads['image_mimes']), 'max:'.$uploads['image_max_kb']],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['receipt' => 'foto struk', 'note' => 'catatan'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['receipt.required' => 'Foto struk kasir wajib dilampirkan saat serah terima.'];
    }
}
