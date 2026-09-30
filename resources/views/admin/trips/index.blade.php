<x-layouts.admin title="Rute">
    <form method="GET" class="card p-4 mb-4 flex flex-col md:flex-row gap-3">
        <div class="relative flex-1"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span><input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kode, tujuan" class="input input-sm pl-10"></div>
        <select name="status" class="input input-sm md:w-48"><option value="">Semua status</option>@foreach ($statuses as $s)<option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>@endforeach</select>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
    <x-ui.card :padding="false">
        @if ($trips->isEmpty())
            <x-ui.empty-state icon="route" title="Tidak ada rute yang cocok" />
        @else
            <div class="overflow-x-auto"><table class="table"><thead><tr><th>Kode</th><th>Rute</th><th>Relawan</th><th>Berangkat</th><th>Slot</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach ($trips as $trip)
                    <tr>
                        <td class="font-semibold whitespace-nowrap">{{ $trip->code }}</td>
                        <td class="max-w-[260px]"><p class="truncate font-medium">{{ $trip->routeLabel() }}</p><p class="text-xs text-muted">{{ $trip->campus }} &bull; {{ $trip->category->name }}</p></td>
                        <td>{{ $trip->fulfiller->shortName() }}</td>
                        <td class="text-xs whitespace-nowrap">{{ $trip->departure_at->translatedFormat('d M H:i') }}</td>
                        <td class="tabular">{{ $trip->active_orders_count }}/{{ $trip->max_slots }} <span class="text-xs text-muted">({{ $trip->orders_count }} total)</span></td>
                        <td><x-ui.status-badge :status="$trip->status" :icon="false" class="h-5 px-2 text-[10px]" /></td>
                        <td class="text-right">
                            @if ($trip->status !== \App\Enums\TripStatus::Cancelled)
                                <x-ui.confirm-form :action="route('admin.trips.cancel', $trip)" :title="'Batalkan rute '.$trip->code.'?'" message="Titipan yang belum dibayar ikut dibatalkan." confirm="Batalkan" variant="danger" reason button-class="btn btn-xs btn-danger-soft">Batalkan</x-ui.confirm-form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody></table></div>
            <div class="px-4 pb-4">{{ $trips->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.admin>
