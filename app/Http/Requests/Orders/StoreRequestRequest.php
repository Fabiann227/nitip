<?php

namespace App\Http\Requests\Orders;

use App\Enums\PrintBinding;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for posting a REQUEST (order without fulfiller).
 */
class StoreRequestRequest extends FormRequest
{
    private ?ServiceCategory $category = null;

    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->category = ServiceCategory::query()->active()->find($this->input('service_category_id'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->category;
        $orders = config('nitip.orders');

        return [
            'service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'pickup_location' => ['required', 'string', 'max:150'],
            'dropoff_location' => ['required', 'string', 'max:150'],
            'needed_by' => ['required', 'date', 'after:now', 'before:'.now()->addDays(7)->toDateTimeString()],
            'service_fee' => [
                'required', 'integer',
                'min:'.($category?->fee_min ?? $orders['min_service_fee']),
                'max:'.($category?->fee_max ?? $orders['max_service_fee']),
            ],
        ] + self::detailRules($category, $orders);
    }

    /**
     * Item / print / document rules shared with JoinTripRequest.
     *
     * @param  array<string, mixed>  $orders
     * @return array<string, mixed>
     */
    public static function detailRules(?ServiceCategory $category, array $orders): array
    {
        $uploads = config('nitip.uploads');

        if ($category?->isPrint()) {
            return [
                'estimated_item_cost' => ['required', 'integer', 'min:0', 'max:'.$orders['max_item_cost']],
                'print.pages' => ['required', 'integer', 'min:1', 'max:2000'],
                'print.copies' => ['required', 'integer', 'min:1', 'max:50'],
                'print.is_color' => ['nullable', 'boolean'],
                'print.paper_size' => ['required', Rule::in(['A4', 'F4', 'A3', 'A5'])],
                'print.binding' => ['required', Rule::in(PrintBinding::values())],
                'print.instructions' => ['nullable', 'string', 'max:500'],
                'document' => ['required', 'file', 'mimes:'.implode(',', $uploads['document_mimes']), 'max:'.$uploads['document_max_kb']],
                'items' => ['nullable', 'array', 'max:'.$orders['max_items']],
            ];
        }

        return [
            'items' => ['required', 'array', 'min:1', 'max:'.$orders['max_items']],
            'items.*.name' => ['required', 'string', 'max:120'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.estimated_price' => ['nullable', 'integer', 'min:0', 'max:'.$orders['max_item_cost']],
            'items.*.note' => ['nullable', 'string', 'max:255'],
            'estimated_item_cost' => ['nullable', 'integer', 'min:0', 'max:'.$orders['max_item_cost']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return self::detailAttributes() + [
            'service_category_id' => 'kategori layanan',
            'title' => 'judul titipan',
            'notes' => 'catatan',
            'pickup_location' => 'lokasi pembelian',
            'dropoff_location' => 'lokasi serah terima',
            'needed_by' => 'batas waktu',
            'service_fee' => 'biaya jasa',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function detailAttributes(): array
    {
        return [
            'estimated_item_cost' => 'perkiraan biaya',
            'print.pages' => 'jumlah halaman',
            'print.copies' => 'jumlah rangkap',
            'print.paper_size' => 'ukuran kertas',
            'print.binding' => 'jenis jilid',
            'print.instructions' => 'instruksi cetak',
            'document' => 'berkas',
            'items' => 'daftar item',
            'items.*.name' => 'nama item',
            'items.*.quantity' => 'jumlah',
            'items.*.estimated_price' => 'perkiraan harga',
            'items.*.note' => 'catatan item',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'needed_by.after' => 'Batas waktu harus di masa depan.',
            'needed_by.before' => 'Batas waktu maksimal 7 hari ke depan.',
            'items.required' => 'Tambahkan minimal satu item yang ingin dititip.',
            'items.min' => 'Tambahkan minimal satu item yang ingin dititip.',
            'document.required' => 'Unggah berkas yang ingin dicetak.',
            'service_fee.min' => 'Biaya jasa minimal :min sesuai rentang kategori.',
            'service_fee.max' => 'Biaya jasa maksimal :max sesuai rentang kategori.',
        ];
    }
}
