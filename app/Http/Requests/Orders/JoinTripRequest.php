<?php

namespace App\Http\Requests\Orders;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for joining a TRIP (OFFER stream). Category, fee and deadline come from the trip.
 */
class JoinTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Trip|null $trip */
        $trip = $this->route('trip');
        $category = $trip?->category;

        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'pickup_location' => ['nullable', 'string', 'max:150'],
            'dropoff_location' => ['required', 'string', 'max:150'],
        ] + StoreRequestRequest::detailRules($category, config('nitip.orders'));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return StoreRequestRequest::detailAttributes() + [
            'title' => 'judul titipan',
            'notes' => 'catatan',
            'pickup_location' => 'lokasi pembelian',
            'dropoff_location' => 'lokasi serah terima',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu item yang ingin dititip.',
            'document.required' => 'Unggah berkas yang ingin dicetak.',
        ];
    }
}
