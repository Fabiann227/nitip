<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "Sedang diantar": purchase done, record the real price + cashier receipt.
 */
class DeliverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('deliver', $this->route('order')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uploads = config('nitip.uploads');
        $order = $this->route('order');
        $hasItemCost = $order?->category?->has_item_cost ?? true;

        return [
            'actual_item_cost' => [$hasItemCost ? 'required' : 'nullable', 'integer', 'min:0', 'max:'.config('nitip.orders.max_item_cost')],
            'receipt' => [$hasItemCost && ! $order?->hasReceipt() ? 'required' : 'nullable', 'file', 'image', 'mimes:'.implode(',', $uploads['image_mimes']), 'max:'.$uploads['image_max_kb']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['actual_item_cost' => 'biaya riil sesuai struk', 'receipt' => 'foto struk'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'actual_item_cost.required' => 'Masukkan total sesuai struk kasir.',
            'receipt.required' => 'Foto struk kasir wajib dilampirkan sebagai bukti biaya riil.',
        ];
    }
}
