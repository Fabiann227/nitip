@php $editing = $category !== null; @endphp
<x-layouts.admin :title="$editing ? 'Ubah kategori' : 'Kategori baru'">
    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:text-primary mb-4"><span class="material-symbols-outlined text-[16px]">arrow_back</span>Kategori & tarif</a>
    <x-ui.card class="max-w-2xl p-6">
        <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-4" novalidate>
            @csrf @if ($editing)@method('PUT')@endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="name" label="Nama kategori" :value="$category?->name" required />
                <x-ui.input name="code" label="Kode (huruf kecil, _)" :value="$category?->code" required placeholder="food_in_campus" hint="Dipakai internal, unik." />
            </div>
            <x-ui.input name="description" label="Deskripsi" :value="$category?->description" optional maxlength="255" />
            <div class="grid grid-cols-3 gap-4">
                <x-ui.input name="fee_min" type="number" label="Tarif minimum" prefix="Rp" :value="$category?->fee_min ?? 3000" step="500" required />
                <x-ui.input name="fee_default" type="number" label="Tarif default" prefix="Rp" :value="$category?->fee_default ?? 3500" step="500" required />
                <x-ui.input name="fee_max" type="number" label="Tarif maksimum" prefix="Rp" :value="$category?->fee_max ?? 4000" step="500" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="icon" label="Ikon (Material Symbols)" :value="$category?->icon ?? 'shopping_bag'" required hint="Contoh: restaurant, fastfood, print" />
                <x-ui.input name="sort_order" type="number" label="Urutan" :value="$category?->sort_order ?? 0" min="0" />
            </div>
            <div class="flex flex-wrap gap-6 pt-2">
                <x-ui.checkbox name="requires_document" :checked="(bool) old('requires_document', $category?->requires_document)">Wajib unggah berkas (print/fotokopi)</x-ui.checkbox>
                <x-ui.checkbox name="has_item_cost" :checked="(bool) old('has_item_cost', $category?->has_item_cost ?? true)">Ada biaya barang (butuh struk)</x-ui.checkbox>
                <x-ui.checkbox name="is_active" :checked="(bool) old('is_active', $category?->is_active ?? true)">Aktif</x-ui.checkbox>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-border"><a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Batal</a><x-ui.button type="submit" icon="save">Simpan</x-ui.button></div>
        </form>
    </x-ui.card>
</x-layouts.admin>
