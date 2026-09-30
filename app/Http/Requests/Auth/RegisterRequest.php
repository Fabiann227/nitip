<?php

namespace App\Http\Requests\Auth;

use App\Support\Campuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'nim' => trim((string) $this->input('nim')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'nim' => [
                'required', 'string', 'min:5', 'max:30', 'regex:/^[A-Za-z0-9.\-\/]+$/',
                Rule::unique('users', 'nim')->where('campus', $this->input('campus')),
            ],
            'campus' => ['required', 'string', Rule::in(Campuses::codes())],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'whatsapp_number' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['email', 'campus'])) {
                    return;
                }

                if (! Campuses::allowsEmail($this->input('campus'), $this->input('email'))) {
                    $validator->errors()->add(
                        'email',
                        'Gunakan email resmi kampus (berakhiran .ac.id atau domain '.Campuses::domainsLabel($this->input('campus')).').'
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'nim' => 'NIM',
            'campus' => 'universitas',
            'email' => 'email kampus',
            'whatsapp_number' => 'nomor WhatsApp',
            'password' => 'kata sandi',
            'terms' => 'ketentuan layanan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp_number.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'nim.regex' => 'NIM hanya boleh berisi huruf, angka, titik, garis miring, atau strip.',
            'nim.unique' => 'NIM ini sudah terdaftar di kampus tersebut.',
            'terms.accepted' => 'Kamu harus menyetujui Ketentuan Layanan & Kebijakan Privasi.',
        ];
    }
}
