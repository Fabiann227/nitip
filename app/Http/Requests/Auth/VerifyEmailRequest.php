<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $digits = $this->input('digits');

        if (is_array($digits)) {
            $this->merge(['code' => implode('', array_map(fn ($d) => trim((string) $d), $digits))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $length = (int) config('nitip.otp.length', 6);

        return [
            'code' => ['required', 'digits:'.$length],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Masukkan 6 digit kode OTP.',
            'code.digits' => 'Kode OTP harus terdiri dari 6 angka.',
        ];
    }
}
