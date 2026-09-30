@php $editing = $trip !== null; $initialCategory = (int) old('service_category_id', $trip?->service_category_id ?? $categories->first()?->id); @endphp
<x-layouts.app :title="$editing ? 'Ubah Rute' : 'Posting Rute'">
    <x-ui.page-header eyebrow="Jalur Jastiper · OFFER" :title="$editing ? 'Ubah rute '.$trip->code : 'Posting rute kamu'" subtitle="Udah mau jalan? Beri tahu teman sekampus tujuanmu dan berapa titipan yang bisa kamu bawa." :back="$editing ? route('trips.show', $trip) : route('dashboard')" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form method="POST" action="{{ $editing ? route('trips.update', $trip) : route('trips.store') }}" class="lg:col-span-2 card p-5 sm:p-6 space-y-8" novalidate
              x-data="{
                  categories: @js($categories->mapWithKeys(fn ($c) => [$c->id => ['min' => $c->fee_min, 'max' => $c->fee_max, 'default' => $c->fee_default]])),
                  categoryId: {{ $initialCategory ?: 'null' }},
                  fee: {{ (int) old('service_fee', $editing ? $trip->service_fee : ($categories->first()?->fee_default ?? 3500)) }},
                  departure: @js(old('departure_at', $trip?->departure_at?->format('Y-m-d\TH:i') ?? now()->addHour()->format('Y-m-d\TH:i'))),
                  closeBefore: {{ (int) $closeBefore }},
                  get category() { return this.categoryId ? this.categories[this.categoryId] : null },
                  select(id) { this.categoryId = id; if (this.category) this.fee = this.category.default; },
                  get closesLabel() {
                      if (!this.departure) return '-';
                      const d = new Date(this.departure); if (isNaN(d)) return '-';
                      const c = new Date(d.getTime() - this.closeBefore * 60000);
                      return c.toLocaleString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                  }
              }">
            @csrf
            @if ($editing)@method('PUT')@endif

            <section>
                <h2 class="font-sans font-bold text-base mb-1">1. Titipan apa yang kamu terima?</h2>
                <p class="text-sm text-on-surface-variant mb-3">Pilih satu kategori sesuai tujuanmu.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach ($categories as $category)
                        <label class="relative flex flex-col gap-2 p-4 rounded-2xl border-2 cursor-pointer transition-all hover:shadow-float" :class="categoryId === {{ $category->id }} ? 'border-primary bg-primary-fixed/20' : 'border-border bg-white'">
                            <input type="radio" name="service_category_id" value="{{ $category->id }}" class="sr-only" x-on:change="select({{ $category->id }})" @checked($initialCategory === $category->id) @disabled($editing && $trip->active_orders_count > 0 && $trip->service_category_id !== $category->id)>
                            <span class="flex items-center justify-between"><span class="w-10 h-10 rounded-xl bg-white shadow-sm text-primary flex items-center justify-center"><span class="material-symbols-outlined">{{ $category->icon }}</span></span><span class="material-symbols-outlined text-primary" x-show="categoryId === {{ $category->id }}">check_circle</span></span>
                            <span class="font-semibold text-sm">{{ $category->name }}</span>
                            <span class="text-xs text-on-surface-variant">{{ $category->feeRange() }}</span>
                        </label>
                    @endforeach
                </div>
                <x-ui.field-error name="service_category_id" />
            </section>

            <section class="space-y-4">
                <h2 class="font-sans font-bold text-base">2. Tujuan &amp; waktu</h2>
                <x-ui.location-input name="destination" label="Tujuan (tempat beli / cetak)" :locations="$locations" :value="$trip?->destination" icon="storefront" placeholder="Contoh: Food Court UPH (Gedung B Lt. 1)" />
                <x-ui.input name="waypoints" label="Lewat mana saja?" optional :value="$trip?->waypoints" placeholder="Contoh: Perpustakaan → Gedung C → Food Court" icon="alt_route" maxlength="255" hint="Bantu penitip tahu apakah rutemu searah." />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="departure_at" type="datetime-local" label="Waktu berangkat" x-model="departure" required :min="now()->format('Y-m-d\TH:i')" />
                    <div class="p-3 rounded-xl bg-surface-low flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">timer</span>
                        <div class="text-sm"><p class="font-semibold text-on-surface">Batas terima titipan</p><p class="text-on-surface-variant">Otomatis <span class="font-semibold text-on-surface" x-text="closesLabel"></span> ({{ $closeBefore }} menit sebelum berangkat).</p></div>
                    </div>
                </div>
                <x-ui.select name="transport_mode" label="Moda transportasi" required>
                    @foreach (\App\Enums\TransportMode::cases() as $mode)<option value="{{ $mode->value }}" @selected(old('transport_mode', $trip?->transport_mode?->value ?? 'walk') === $mode->value)>{{ $mode->label() }}</option>@endforeach
                </x-ui.select>
            </section>

            <section class="space-y-4">
                <h2 class="font-sans font-bold text-base">3. Kuota &amp; biaya</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="max_slots" type="number" label="Kuota titipan" :value="old('max_slots', $trip?->max_slots ?? 3)" min="1" :max="config('nitip.trips.max_slots')" required :hint="$editing ? 'Tidak boleh kurang dari titipan aktif ('.$trip->active_orders_count.').' : 'Berapa titipan yang sanggup kamu bawa.'" />
                    <div>
                        <x-ui.input name="service_fee" type="number" label="Biaya jasa per titipan" prefix="Rp" x-model.number="fee" step="500" required :disabled="$editing && $trip->active_orders_count > 0" />
                        <p class="hint" x-show="category">Rentang kategori: <span x-text="category ? rupiah(category.min) + ' - ' + rupiah(category.max) : ''"></span></p>
                        @if ($editing && $trip->active_orders_count > 0)<input type="hidden" name="service_fee" value="{{ $trip->service_fee }}"><p class="hint">Biaya tidak bisa diubah karena sudah ada titipan aktif.</p>@endif
                    </div>
                </div>
                <x-ui.textarea name="notes" label="Catatan untuk penitip" optional rows="2" :value="$trip?->notes" placeholder="Contoh: hanya bawa yang muat di tas, bisa sekalian Kopi Kenangan" maxlength="1000" />
            </section>

            <div class="pt-5 border-t border-border flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2">
                <x-ui.button :href="$editing ? route('trips.show', $trip) : route('dashboard')" variant="outline">Batal</x-ui.button>
                <x-ui.button type="submit" icon-right="add_road">{{ $editing ? 'Simpan perubahan' : 'Tayangkan rute' }}</x-ui.button>
            </div>
        </form>

        <aside class="space-y-4">
            <x-ui.card title="Yang perlu kamu tahu" icon="help">
                <ul class="space-y-3 text-sm text-on-surface-variant">
                    <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">account_balance_wallet</span>Penitip mentransfer langsung ke metode pembayaranmu. Pastikan sudah diatur di <a href="{{ route('settings.payment') }}" class="text-primary font-semibold">pengaturan</a>.</li>
                    <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">receipt_long</span>Simpan struk kasir setiap pembelian, kamu memfotonya dan mencatat harga riil sebelum mengantar.</li>
                    <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">backpack</span>Kuota otomatis berkurang saat penitip bergabung dan kembali saat pesanan dibatalkan.</li>
                    <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">percent</span>Kamu menerima 100 % biaya jasa, tanpa potongan.</li>
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
