<x-layouts.admin title="Pesanan">
    <form method="GET" class="card p-4 mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2 relative"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span><input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode, judul, lokasi" class="input input-sm pl-10"></div>
        <select name="status" class="input input-sm"><option value="">Semua status</option>@foreach ($statuses as $s)<option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>@endforeach<option value="refund" @selected($filters['status'] === 'refund')>Perlu refund</option></select>
        <select name="category" class="input input-sm"><option value="">Semua kategori</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected($filters['category'] === $c->id)>{{ $c->name }}</option>@endforeach</select>
        <div class="flex gap-2"><select name="campus" class="input input-sm"><option value="">Semua kampus</option>@foreach ($campuses as $code => $c)<option value="{{ $code }}" @selected($filters['campus'] === $code)>{{ $code }}</option>@endforeach</select><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
    </form>
    <x-ui.card :padding="false">
        @if ($orders->isEmpty())
            <x-ui.empty-state icon="receipt_long" title="Tidak ada pesanan yang cocok" />
        @else
            <div class="overflow-x-auto"><table class="table"><thead><tr><th>Kode</th><th>Titipan</th><th>Penitip</th><th>Relawan</th><th>Status</th><th class="text-right">Total</th><th>Dibuat</th><th></th></tr></thead><tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary whitespace-nowrap">{{ $order->code }}</a>@if ($order->needs_refund)<x-ui.badge variant="danger" class="h-5 px-2 text-[10px] ml-1">Refund</x-ui.badge>@endif</td>
                        <td class="max-w-[220px]"><p class="truncate font-medium">{{ $order->title }}</p><p class="text-xs text-muted">{{ $order->category->name }} &bull; {{ $order->campus }}</p></td>
                        <td>{{ $order->requester->shortName() }}</td>
                        <td>{{ $order->fulfiller?->shortName() ?? '-' }}</td>
                        <td><x-ui.status-badge :status="$order->status" :icon="false" class="h-5 px-2 text-[10px]" /></td>
                        <td class="text-right tabular">{{ rupiah($order->finalTotal()) }}</td>
                        <td class="text-xs text-muted whitespace-nowrap">{{ $order->created_at->translatedFormat('d M H:i') }}</td>
                        <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-outline">Detail</a></td>
                    </tr>
                @endforeach
            </tbody></table></div>
            <div class="px-4 pb-4">{{ $orders->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.admin>
