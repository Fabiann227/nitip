<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('complete', $this->route('order')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $digits = $this->input('digits');

        if (is_array($digits)) {
            $this->merge(['pin' => implode('', array_map(fn ($d) => trim((string) $d), $digits))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $order = $this->route('order');
        $isFulfiller = $order && $this->user() && $order->isFulfiller($this->user());

        return [
            'pin' => [$isFulfiller ? 'required' : 'nullable', 'digits:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pin.required' => 'Masukkan PIN 4 digit dari penitip.',
            'pin.digits' => 'PIN harus 4 angka.',
        ];
    }
}
