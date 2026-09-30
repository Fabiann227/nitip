<x-layouts.app title="Pesanan Saya">
    <x-ui.page-header title="Pesanan saya" subtitle="Semua titipan yang kamu buat dan yang kamu bantu, lengkap dengan statusnya.">
        <x-ui.button :href="route('requests.create')" size="sm" icon="add_circle">Titipan baru</x-ui.button>
    </x-ui.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <x-ui.tab-link :href="route('orders.index', ['role' => 'requester', 'status' => $filter])" :active="$role === 'requester'" icon="shopping_bag" :count="$counts['requester']">Sebagai penitip</x-ui.tab-link>
        <x-ui.tab-link :href="route('orders.index', ['role' => 'fulfiller', 'status' => $filter])" :active="$role === 'fulfiller'" icon="directions_walk" :count="$counts['fulfiller']">Sebagai relawan</x-ui.tab-link>
        <form method="GET" class="ml-auto w-full sm:w-64">
            <input type="hidden" name="role" value="{{ $role }}"><input type="hidden" name="status" value="{{ $filter }}">
            <div class="relative"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span><input type="search" name="q" value="{{ $q }}" placeholder="Cari kode / judul" class="input input-sm pl-10"></div>
        </form>
    </div>
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 mb-4">
        @foreach (['active' => 'Aktif', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'] as $key => $label)
            <x-ui.chip :href="route('orders.index', ['role' => $role, 'status' => $key, 'q' => $q])" :active="$filter === $key">{{ $label }}</x-ui.chip>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-ui.card>
            @if ($role === 'requester')
                <x-ui.empty-state icon="shopping_bag" title="Belum ada titipan di sini" description="Kalau lagi mager, posting kebutuhanmu dan biarkan relawan yang searah membantu.">
                    <x-ui.button :href="route('requests.create')" size="sm">Posting kebutuhan</x-ui.button>
                    <x-ui.button :href="route('explore', ['tab' => 'trips'])" size="sm" variant="outline">Lihat rute relawan</x-ui.button>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="directions_walk" title="Belum ada titipan yang kamu bantu" description="Ambil permintaan yang searah jalanmu atau buka rute untuk dapat uang saku tambahan.">
                    <x-ui.button :href="route('explore')" size="sm">Cari permintaan</x-ui.button>
                    <x-ui.button :href="route('trips.create')" size="sm" variant="outline">Buka rute</x-ui.button>
                </x-ui.empty-state>
            @endif
        </x-ui.card>
    @else
        <div class="space-y-2">
            @foreach ($orders as $order)
                <x-cards.order-row :order="$order" :perspective="$role" />
            @endforeach
        </div>
        {{ $orders->links() }}
    @endif
</x-layouts.app>
