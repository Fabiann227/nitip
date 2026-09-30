<x-layouts.app title="Nitip" wide>
    <x-ui.page-header eyebrow="Live campus activity" title="Nitip" subtitle="Pilih mau bantu titipan teman (Permintaan) atau tumpangi rute relawan yang sedang berangkat (Rute).">
        <x-ui.button :href="route('requests.create')" size="sm" icon="add_circle">Posting kebutuhan</x-ui.button>
        <x-ui.button :href="route('trips.create')" size="sm" variant="secondary" icon="add_road">Posting rute</x-ui.button>
    </x-ui.page-header>

    <form method="GET" action="{{ route('explore') }}" class="space-y-4 mb-6">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.tab-link :href="route('explore', array_filter(['tab' => 'requests', 'category' => $filters['category'], 'campus' => $filters['campus'], 'q' => $filters['q']]))" :active="$tab === 'requests'" icon="shopping_bag" :count="$counts['requests']">Permintaan</x-ui.tab-link>
            <x-ui.tab-link :href="route('explore', array_filter(['tab' => 'trips', 'category' => $filters['category'], 'campus' => $filters['campus'], 'q' => $filters['q']]))" :active="$tab === 'trips'" icon="directions_walk" :count="$counts['trips']">Rute relawan</x-ui.tab-link>
            <div class="flex-1"></div>
            <div class="relative w-full sm:w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari judul, lokasi, kode..." class="input input-sm pl-10">
            </div>
            <select name="campus" class="input input-sm w-auto" onchange="this.form.submit()">
                <option value="">Semua kampus</option>
                @foreach ($campuses as $code => $campus)
                    <option value="{{ $code }}" @selected($filters['campus'] === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <x-ui.chip :href="route('explore', array_filter(['tab' => $tab, 'campus' => $filters['campus'], 'q' => $filters['q']]))" :active="! $filters['category']" icon="apps">Semua kategori</x-ui.chip>
            @foreach ($categories as $category)
                <x-ui.chip :href="route('explore', array_filter(['tab' => $tab, 'category' => $category->id, 'campus' => $filters['campus'], 'q' => $filters['q']]))" :active="$filters['category'] === $category->id" :icon="$category->icon">{{ $category->name }}</x-ui.chip>
            @endforeach
        </div>
    </form>

    @if ($tab === 'requests')
        @if ($requests->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="search_off" title="Belum ada permintaan yang cocok" description="Coba ubah filter kategori atau kampus. Atau posting kebutuhanmu supaya relawan bisa membantu.">
                    <x-ui.button :href="route('requests.create')" size="sm">Posting kebutuhan</x-ui.button>
                    @if ($filters['q'] || $filters['category'])<x-ui.button :href="route('explore')" size="sm" variant="outline">Reset filter</x-ui.button>@endif
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($requests as $order)
                    <x-cards.order-card :order="$order" />
                @endforeach
            </div>
            {{ $requests->links() }}
        @endif
    @else
        @if ($trips->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="route" title="Belum ada rute terbuka" description="Belum ada relawan yang memposting rute untuk filter ini. Kamu bisa membuka rute sendiri dan dapat uang saku tambahan.">
                    <x-ui.button :href="route('trips.create')" size="sm">Buka rute</x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($trips as $trip)
                    <x-cards.trip-card :trip="$trip" />
                @endforeach
            </div>
            {{ $trips->links() }}
        @endif
    @endif
</x-layouts.app>
