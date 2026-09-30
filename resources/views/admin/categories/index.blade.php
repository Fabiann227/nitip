<x-layouts.admin title="Kategori & tarif">
    <x-slot:actions><a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary"><span class="material-symbols-outlined text-[18px]">add</span>Kategori baru</a></x-slot:actions>
    <p class="text-sm text-on-surface-variant mb-4">Skema flat fee dari PDF: rentang tarif rekomendasi membatasi biaya jasa yang bisa dipilih pengguna.</p>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto"><table class="table"><thead><tr><th>Kategori</th><th>Kode</th><th>Tarif min</th><th>Default</th><th>Tarif maks</th><th>Berkas</th><th>Pesanan</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach ($categories as $category)
                <tr>
                    <td><div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">{{ $category->icon }}</span><div><p class="font-semibold">{{ $category->name }}</p><p class="text-xs text-muted max-w-xs truncate">{{ $category->description }}</p></div></div></td>
                    <td><code class="text-xs">{{ $category->code }}</code></td>
                    <td class="tabular">{{ rupiah($category->fee_min) }}</td>
                    <td class="tabular font-semibold">{{ rupiah($category->fee_default) }}</td>
                    <td class="tabular">{{ rupiah($category->fee_max) }}</td>
                    <td>{{ $category->requires_document ? 'Wajib' : '-' }}</td>
                    <td class="tabular">{{ $category->orders_count }}</td>
                    <td>@if ($category->is_active)<x-ui.badge variant="success" class="h-5 px-2 text-[10px]">Aktif</x-ui.badge>@else<x-ui.badge variant="neutral" class="h-5 px-2 text-[10px]">Nonaktif</x-ui.badge>@endif</td>
                    <td class="text-right whitespace-nowrap"><a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-xs btn-outline">Ubah</a> <x-ui.confirm-form :action="route('admin.categories.destroy', $category)" method="DELETE" :title="'Hapus kategori '.$category->name.'?'" message="Jika kategori sudah dipakai pesanan, kategori hanya dinonaktifkan." confirm="Hapus" variant="danger" button-class="btn btn-xs btn-danger-soft">Hapus</x-ui.confirm-form></td>
                </tr>
            @endforeach
        </tbody></table></div>
    </x-ui.card>
</x-layouts.admin>
