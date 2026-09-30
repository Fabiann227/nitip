<x-layouts.admin title="Audit trail">
    <p class="text-sm text-on-surface-variant mb-4">Pencatatan permanen setiap transisi status pesanan di tabel <code>order_events</code>: siapa, kapan, dari status apa ke apa. Menjadi rekaman legal untuk sanggahan dan rekonsiliasi selisih nota.</p>
    <form method="GET" class="card p-4 mb-4 flex flex-col md:flex-row gap-3">
        <div class="relative flex-1"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span><input type="search" name="q" value="{{ $q }}" placeholder="Cari deskripsi / kode pesanan" class="input input-sm pl-10"></div>
        <select name="type" class="input input-sm md:w-56"><option value="">Semua jenis event</option>@foreach ($types as $t)<option value="{{ $t->value }}" @selected($type === $t->value)>{{ $t->value }}</option>@endforeach</select>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="table"><thead><tr><th>Waktu</th><th>Pesanan</th><th>Event</th><th>Status</th><th>Pelaku</th><th>Deskripsi</th></tr></thead><tbody>
                @forelse ($events as $event)
                    <tr>
                        <td class="text-xs text-muted whitespace-nowrap">{{ $event->created_at->translatedFormat('d M Y H:i:s') }}</td>
                        <td><a href="{{ route('admin.orders.show', $event->order) }}" class="font-semibold text-primary">{{ $event->order->code }}</a></td>
                        <td><code class="text-xs">{{ $event->type->value }}</code></td>
                        <td class="text-xs whitespace-nowrap">{{ $event->from_status?->label() ?? '-' }} &rarr; {{ $event->to_status?->label() ?? '-' }}</td>
                        <td class="text-sm">{{ $event->actor?->shortName() ?? 'Sistem' }}</td>
                        <td class="text-sm max-w-md">{{ $event->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-8">Belum ada event.</td></tr>
                @endforelse
            </tbody></table>
        </div>
        <div class="px-4 pb-4">{{ $events->links() }}</div>
    </x-ui.card>
</x-layouts.admin>
