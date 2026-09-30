<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'whatsapp_number' => 'nomor WhatsApp',
            'bio' => 'bio',
            'avatar' => 'foto profil',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp_number.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890.',
        ];
    }
}
