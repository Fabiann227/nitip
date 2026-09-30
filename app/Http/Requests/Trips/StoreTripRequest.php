<?php

namespace App\Http\Requests\Trips;

use App\Enums\TransportMode;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTripRequest extends FormRequest
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
        $orders = config('nitip.orders');

        return [
            'service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'destination' => ['required', 'string', 'max:120'],
            'waypoints' => ['nullable', 'string', 'max:255'],
            'departure_at' => ['required', 'date', 'after:now', 'before:'.now()->addDays(7)->toDateTimeString()],
            'transport_mode' => ['required', Rule::in(TransportMode::values())],
            'max_slots' => ['required', 'integer', 'min:1', 'max:'.config('nitip.trips.max_slots')],
            'service_fee' => ['required', 'integer', 'min:'.$orders['min_service_fee'], 'max:'.$orders['max_service_fee']],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['service_category_id', 'service_fee'])) {
                    return;
                }

                $category = ServiceCategory::query()->find($this->input('service_category_id'));
                $fee = (int) $this->input('service_fee');

                if ($category && ($fee < $category->fee_min || $fee > $category->fee_max)) {
                    $validator->errors()->add('service_fee', 'Biaya jasa harus di rentang '.$category->feeRange().' sesuai kategori.');
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
            'service_category_id' => 'kategori titipan',
            'destination' => 'tujuan',
            'waypoints' => 'rute yang dilewati',
            'departure_at' => 'waktu berangkat',
            'transport_mode' => 'moda transportasi',
            'max_slots' => 'kuota titipan',
            'service_fee' => 'biaya jasa per titipan',
            'notes' => 'catatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'departure_at.after' => 'Waktu berangkat harus di masa depan.',
            'departure_at.before' => 'Waktu berangkat maksimal 7 hari ke depan.',
            'service_category_id.required' => 'Pilih kategori titipan yang kamu terima.',
        ];
    }
}
