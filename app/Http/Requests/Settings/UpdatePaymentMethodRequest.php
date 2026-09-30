<?php

namespace App\Http\Requests\Settings;

use App\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = PaymentMethodType::tryFrom((string) $this->input('type'));
        $uploads = config('nitip.uploads');
        $needsQris = $type === PaymentMethodType::Qris && ! $this->user()?->hasQris();

        return [
            'type' => ['required', Rule::in(PaymentMethodType::values())],
            'provider_name' => [$type?->requiresProviderName() ? 'required' : 'nullable', 'string', 'max:60'],
            'account_number' => [$type?->requiresAccountNumber() ? 'required' : 'nullable', 'string', 'max:60', 'regex:/^[0-9+\-\s]+$/'],
            'account_name' => ['required', 'string', 'max:100'],
            'qris_image' => [$needsQris ? 'required' : 'nullable', 'file', 'image', 'mimes:'.implode(',', $uploads['image_mimes']), 'max:'.$uploads['image_max_kb']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'jenis metode',
            'provider_name' => 'nama bank',
            'account_number' => 'nomor rekening / e-wallet',
            'account_name' => 'nama pemilik',
            'qris_image' => 'gambar QRIS',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_number.regex' => 'Nomor hanya boleh berisi angka.',
            'qris_image.required' => 'Unggah gambar kode QRIS kamu.',
        ];
    }
}
