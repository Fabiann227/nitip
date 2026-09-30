{{--
    Shared order form fields.
    REQUEST mode  : $categories (Collection), $locations (list<string>), $selectedCategory (?int)
    JOIN mode     : $trip (Trip) + $category (ServiceCategory fixed by the trip), $locations
--}}
@php
    $isJoin = isset($trip) && $trip;
    $categoryList = $isJoin ? collect([$category]) : $categories;
    $categoryData = $categoryList->mapWithKeys(fn ($c) => [$c->id => [
        'id' => $c->id, 'name' => $c->name, 'print' => $c->isPrint(), 'hasItemCost' => $c->has_item_cost,
        'feeMin' => $c->fee_min, 'feeDefault' => $c->fee_default, 'feeMax' => $c->fee_max,
    ]]);
    $initialCategory = $isJoin ? $category->id : (int) old('service_category_id', $selectedCategory ?? $categories->first()?->id);
    $oldItems = old('items', []);
@endphp
<div x-data="{
        categories: @js($categoryData),
        categoryId: {{ $initialCategory ?: 'null' }},
        fee: {{ (int) old('service_fee', $categoryList->firstWhere('id', $initialCategory)?->fee_default ?? 3500) }},
        get category() { return this.categoryId ? this.categories[this.categoryId] : null },
        selectCategory(id) { this.categoryId = id; if (this.category) this.fee = this.category.feeDefault; },
    }" class="space-y-8">

    {{-- 1. Category --}}
    <section>
        <h2 class="font-sans font-bold text-base mb-1">1. Layanan</h2>
        @if ($isJoin)
            <input type="hidden" name="service_category_id" value="{{ $category->id }}">
            <div class="flex items-center gap-3 p-4 rounded-2xl border-2 border-primary bg-primary-fixed/20">
                <span class="w-10 h-10 rounded-xl bg-white shadow-sm text-primary flex items-center justify-center"><span class="material-symbols-outlined">{{ $category->icon }}</span></span>
                <div><p class="font-semibold text-sm">{{ $category->name }}</p><p class="text-xs text-on-surface-variant">Rute ini menerima kategori tersebut. Biaya jasa {{ rupiah($trip->service_fee) }} per titipan.</p></div>
            </div>
        @else
            <p class="text-sm text-on-surface-variant mb-3">Biaya jasa otomatis mengikuti rentang kategori.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach ($categories as $cat)
                    <label class="relative flex flex-col gap-2 p-4 rounded-2xl border-2 cursor-pointer transition-all hover:shadow-float"
                           :class="categoryId === {{ $cat->id }} ? 'border-primary bg-primary-fixed/20' : 'border-border bg-white'">
                        <input type="radio" name="service_category_id" value="{{ $cat->id }}" class="sr-only" x-on:change="selectCategory({{ $cat->id }})" @checked($initialCategory === $cat->id)>
                        <span class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-white shadow-sm text-primary flex items-center justify-center"><span class="material-symbols-outlined">{{ $cat->icon }}</span></span>
                            <span class="material-symbols-outlined text-primary" x-show="categoryId === {{ $cat->id }}">check_circle</span>
                        </span>
                        <span class="font-semibold text-sm text-on-surface">{{ $cat->name }}</span>
                        <span class="text-xs text-on-surface-variant">{{ $cat->feeRange() }}</span>
                    </label>
                @endforeach
            </div>
            <x-ui.field-error name="service_category_id" />
        @endif
    </section>

    {{-- 2. Details --}}
    <section class="space-y-4">
        <h2 class="font-sans font-bold text-base">2. Detail titipan</h2>
        <x-ui.input name="title" label="Judul titipan" placeholder="Contoh: 2x Ayam Geprek Level 2 + Es Teh" required maxlength="150" hint="Ringkas dan jelas, ini yang dilihat relawan di feed." />

        {{-- Food items --}}
        <div x-show="category && !category.print" x-cloak x-data="itemRows(@js(array_values(array_map(fn ($i) => ['name' => $i['name'] ?? '', 'quantity' => $i['quantity'] ?? 1, 'estimated_price' => $i['estimated_price'] ?? '', 'note' => $i['note'] ?? ''], $oldItems))))" class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="label mb-0">Daftar item <span class="text-error">*</span></span>
                <button type="button" class="btn btn-xs btn-soft" x-on:click="add()"><span class="material-symbols-outlined text-[16px]">add</span>Tambah item</button>
            </div>
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid grid-cols-12 gap-2 p-3 rounded-xl bg-surface-low">
                    <div class="col-span-12 sm:col-span-5">
                        <input type="text" :name="`items[${index}][name]`" x-model="row.name" placeholder="Nama item" class="input input-sm" maxlength="120">
                    </div>
                    <div class="col-span-4 sm:col-span-2">
                        <input type="number" :name="`items[${index}][quantity]`" x-model="row.quantity" min="1" max="99" placeholder="Qty" class="input input-sm" aria-label="Jumlah">
                    </div>
                    <div class="col-span-8 sm:col-span-3">
                        <input type="number" :name="`items[${index}][estimated_price]`" x-model="row.estimated_price" min="0" step="500" placeholder="Perkiraan harga (Rp)" class="input input-sm" aria-label="Perkiraan harga satuan">
                    </div>
                    <div class="col-span-10 sm:col-span-1 flex items-center">
                        <input type="text" :name="`items[${index}][note]`" x-model="row.note" placeholder="Catatan" class="input input-sm" maxlength="255" aria-label="Catatan item">
                    </div>
                    <div class="col-span-2 sm:col-span-1 flex items-center justify-end">
                        <button type="button" class="btn-icon text-error" x-on:click="remove(index)" aria-label="Hapus item"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            </template>
            <x-ui.field-error name="items" />
            @foreach ($errors->get('items.*') as $key => $messages)
                <p class="error-text"><span class="material-symbols-outlined text-[14px]">error</span>{{ $messages[0] }}</p>
            @endforeach
            <div class="flex items-center justify-between text-sm px-1">
                <span class="text-on-surface-variant">Perkiraan biaya barang</span>
                <span class="font-bold text-on-surface tabular" x-text="rupiah(subtotal)"></span>
            </div>
            <div x-show="subtotal === 0" x-cloak>
                <x-ui.input name="estimated_item_cost" type="number" label="Perkiraan total harga barang" prefix="Rp" :value="old('estimated_item_cost', 0)" min="0" step="500" hint="Isi jika kamu tidak tahu harga per item. Boleh kira-kira." />
            </div>
            <x-ui.alert type="info" icon="info" class="text-xs">
                <strong>Harga di sini hanya perkiraan.</strong> Setelah membeli, relawan memasukkan total sesuai struk kasir. Kalau lebih murah relawan mengembalikan selisihnya, kalau lebih mahal kamu menambah saat serah terima.
            </x-ui.alert>
        </div>

        {{-- Print specs --}}
        <div x-show="category && category.print" x-cloak class="space-y-4">
            <x-ui.file-input name="document" label="Berkas yang dicetak" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,image/*" :max-mb="10" hint="PDF/DOC/PPT/XLS/gambar, maksimal 10 MB" icon="picture_as_pdf" />
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <x-ui.input name="print[pages]" type="number" label="Halaman" :value="old('print.pages', 1)" min="1" max="2000" />
                <x-ui.input name="print[copies]" type="number" label="Rangkap" :value="old('print.copies', 1)" min="1" max="50" />
                <x-ui.select name="print[paper_size]" label="Ukuran kertas">
                    @foreach (['A4', 'F4', 'A3', 'A5'] as $size)<option value="{{ $size }}" @selected(old('print.paper_size', 'A4') === $size)>{{ $size }}</option>@endforeach
                </x-ui.select>
                <x-ui.select name="print[binding]" label="Jilid">
                    @foreach (\App\Enums\PrintBinding::cases() as $binding)<option value="{{ $binding->value }}" @selected(old('print.binding', 'none') === $binding->value)>{{ $binding->label() }}</option>@endforeach
                </x-ui.select>
            </div>
            <x-ui.checkbox name="print[is_color]" :checked="(bool) old('print.is_color')">Cetak berwarna</x-ui.checkbox>
            <x-ui.input name="print[instructions]" label="Instruksi cetak" optional placeholder="Contoh: cover buffalo biru, bolak-balik" maxlength="500" />
            <div x-show="category && category.print">
                <x-ui.input name="estimated_item_cost" type="number" label="Perkiraan biaya cetak & kertas" prefix="Rp" :value="old('estimated_item_cost', 0)" min="0" step="500" hint="Biaya riil mengikuti struk dari tempat fotokopi, selisih diselesaikan saat serah terima." />
            </div>
        </div>

        <x-ui.textarea name="notes" label="Catatan untuk relawan" optional rows="2" placeholder="Contoh: sambal dipisah, kalau habis ganti menu serupa" maxlength="1000" />
    </section>

    {{-- 3. Location & time --}}
    <section class="space-y-4">
        <h2 class="font-sans font-bold text-base">3. Lokasi &amp; waktu</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.location-input name="pickup_location" :label="$isJoin ? 'Beli di (opsional)' : 'Beli / cetak di'" :locations="$locations" :value="$isJoin ? $trip->destination : null" icon="storefront" :required="! $isJoin" :hint="$isJoin ? 'Default: tujuan rute relawan.' : null" />
            <x-ui.location-input name="dropoff_location" label="Antar ke" :locations="$locations" icon="pin_drop" hint="Gedung, lantai, atau ruang serah terima." />
        </div>
        @if (! $isJoin)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="needed_by" type="datetime-local" label="Dibutuhkan sebelum" :value="old('needed_by', now()->addHours(2)->format('Y-m-d\TH:i'))" required :min="now()->format('Y-m-d\TH:i')" hint="Maksimal 7 hari ke depan." />
                <div>
                    <x-ui.input name="service_fee" type="number" label="Biaya jasa untuk relawan" prefix="Rp" x-model.number="fee" step="500" required />
                    <p class="hint" x-show="category">Rentang kategori: <span x-text="category ? rupiah(category.feeMin) + ' - ' + rupiah(category.feeMax) : ''"></span>. Relawan menerima 100 %.</p>
                </div>
            </div>
        @else
            <x-ui.alert type="info" icon="schedule">Relawan berangkat <strong>{{ $trip->departure_at->translatedFormat('l, d M Y H:i') }}</strong>. Biaya jasa rute ini <strong>{{ rupiah($trip->service_fee) }}</strong> per titipan.</x-ui.alert>
        @endif
    </section>
</div>
