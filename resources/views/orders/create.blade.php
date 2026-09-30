<x-layouts.app title="Posting Kebutuhan">
    <x-ui.page-header eyebrow="Jalur Penitip · REQUEST" title="Posting kebutuhanmu" subtitle="Ceritakan apa yang kamu butuhkan. Relawan yang searah akan mengambil permintaan ini, lalu kamu membayar langsung ke relawan." :back="route('dashboard')" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" class="lg:col-span-2 card p-5 sm:p-6" novalidate x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @include('orders.partials.item-form', ['trip' => null])

            <div class="mt-8 pt-5 border-t border-border flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2">
                <x-ui.button :href="route('dashboard')" variant="outline">Batal</x-ui.button>
                <x-ui.button type="submit" icon-right="campaign" x-bind:disabled="submitting">
                    <span x-show="!submitting">Tayangkan permintaan</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2"><span class="w-4 h-4 rounded-full border-2 border-white/40 border-t-white animate-spin"></span>Menyimpan...</span>
                </x-ui.button>
            </div>
        </form>

        <aside class="space-y-4">
            <x-ui.card title="Bagaimana kelanjutannya?" icon="help">
                <ol class="space-y-3 text-sm text-on-surface-variant">
                    <li class="flex gap-2"><span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>Permintaan tayang di feed kampusmu. Relawan yang searah meng-<em>claim</em>.</li>
                    <li class="flex gap-2"><span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>Kamu transfer biaya jasa + perkiraan barang langsung ke relawan, unggah bukti.</li>
                    <li class="flex gap-2"><span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>Relawan membeli/mencetak, mengantar, dan melampirkan struk riil.</li>
                    <li class="flex gap-2"><span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center shrink-0">4</span>Sebutkan PIN 4 digit saat serah terima, selisih nota diselesaikan di tempat.</li>
                </ol>
            </x-ui.card>
            <x-ui.card class="p-4 bg-warning-container border-warning-border">
                <p class="text-sm text-warning font-semibold mb-1"><span class="material-symbols-outlined text-[18px] align-middle">tips_and_updates</span> Tips biar cepat diambil</p>
                <ul class="text-xs text-warning space-y-1 list-disc list-inside">
                    <li>Beri biaya jasa di batas atas untuk lokasi jauh.</li>
                    <li>Tulis lokasi serah terima yang spesifik (gedung + lantai).</li>
                    <li>Pilih batas waktu realistis, minimal 30 menit.</li>
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
