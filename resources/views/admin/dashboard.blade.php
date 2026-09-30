<x-layouts.admin title="Dashboard">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <x-ui.stat-card label="Mahasiswa terdaftar" :value="$stats['users']" icon="group" :hint="$stats['verified'].' terverifikasi · '.$stats['suspended'].' ditangguhkan'" :href="route('admin.users.index')" />
        <x-ui.stat-card label="Total pesanan" :value="$stats['orders']" icon="receipt_long" tone="secondary" :hint="$stats['orders_active'].' aktif · '.$stats['open_requests'].' mencari relawan'" :href="route('admin.orders.index')" />
        <x-ui.stat-card label="Completion rate" :value="$stats['completion_rate'].' %'" icon="task_alt" tone="info" :hint="$stats['completed'].' selesai / '.$stats['cancelled'].' batal (target 85 %)'" />
        <x-ui.stat-card label="Sengketa terbuka" :value="$stats['open_disputes']" icon="gavel" :tone="$stats['open_disputes'] > 0 ? 'danger' : 'neutral'" :hint="$stats['needs_refund'].' pesanan perlu refund'" :href="route('admin.disputes.index')" />
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-ui.stat-card label="Nilai transaksi selesai" :value="rupiah($stats['gmv'])" icon="payments" tone="neutral" hint="jasa + barang (GMV)" />
        <x-ui.stat-card label="Uang saku relawan" :value="rupiah($stats['fees'])" icon="savings" tone="neutral" hint="0 % komisi platform" />
        <x-ui.stat-card label="CSAT" :value="$stats['avg_rating'] ? number_format($stats['avg_rating'], 2).' / 5' : '-'" icon="star" tone="warning" :hint="$stats['reviews'].' ulasan (target 4.0)'" />
        <x-ui.stat-card label="Rute terbuka" :value="$stats['open_trips']" icon="route" tone="secondary" :hint="'+'.$stats['new_users_week'].' pengguna baru 7 hari'" :href="route('admin.trips.index')" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <x-ui.card title="Aktivitas 14 hari terakhir" subtitle="Pesanan dibuat vs selesai per hari" icon="bar_chart">
                <div class="flex items-end gap-1 sm:gap-2 h-44" role="img" aria-label="Grafik pesanan harian">
                    @foreach ($days as $day)
                        <div class="flex-1 flex flex-col items-center gap-1 min-w-0" title="{{ $day['label'] }}: {{ $day['created'] }} dibuat, {{ $day['completed'] }} selesai">
                            <div class="w-full flex items-end justify-center gap-0.5 h-32">
                                <div class="w-1/2 rounded-t bg-surface-highest" style="height: {{ max(3, round($day['created'] / $maxDay * 120)) }}px"></div>
                                <div class="w-1/2 rounded-t bg-primary" style="height: {{ max(3, round($day['completed'] / $maxDay * 120)) }}px"></div>
                            </div>
                            <span class="text-[9px] text-muted truncate w-full text-center">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-3 text-xs text-muted"><span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-surface-highest"></span>Dibuat</span><span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-primary"></span>Selesai</span></div>
            </x-ui.card>

            <x-ui.card title="Pesanan terbaru" icon="receipt_long" :padding="false">
                <x-slot:actions><a href="{{ route('admin.orders.index') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Kode</th><th>Titipan</th><th>Penitip</th><th>Relawan</th><th>Status</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary">{{ $order->code }}</a></td>
                                    <td class="max-w-[220px] truncate">{{ $order->title }}</td>
                                    <td>{{ $order->requester->shortName() }}</td>
                                    <td>{{ $order->fulfiller?->shortName() ?? '-' }}</td>
                                    <td><x-ui.status-badge :status="$order->status" :icon="false" class="h-5 px-2 text-[10px]" /></td>
                                    <td class="text-right tabular">{{ rupiah($order->estimatedTotal()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
        <div class="space-y-6">
            <x-ui.card title="Distribusi status" icon="donut_small">
                <ul class="space-y-2">
                    @foreach ($byStatus as $row)
                        <li class="flex items-center justify-between text-sm"><x-ui.status-badge :status="$row['status']" :icon="false" class="h-6" /><span class="font-semibold tabular">{{ $row['total'] }}</span></li>
                    @endforeach
                </ul>
            </x-ui.card>
            <x-ui.card title="Sengketa perlu tinjauan" icon="gavel" :padding="false">
                <div class="divide-y divide-border">
                    @forelse ($openDisputes as $dispute)
                        <a href="{{ route('admin.disputes.show', $dispute) }}" class="block p-4 hover:bg-canvas"><p class="text-sm font-semibold">{{ $dispute->reason->label() }}</p><p class="text-xs text-muted">#{{ $dispute->order->code }} &bull; {{ $dispute->openedBy->shortName() }} &bull; {{ $dispute->created_at->diffForHumans() }}</p></a>
                    @empty
                        <p class="p-4 text-sm text-muted">Tidak ada sengketa terbuka. 🎉</p>
                    @endforelse
                </div>
            </x-ui.card>
            <x-ui.card title="Relawan teraktif" icon="emoji_events" :padding="false">
                <div class="divide-y divide-border">
                    @foreach ($topFulfillers as $i => $u)
                        <a href="{{ route('admin.users.show', $u) }}" class="flex items-center gap-3 p-3 hover:bg-canvas"><span class="w-6 text-center font-bold text-muted">{{ $i + 1 }}</span><x-ui.avatar :user="$u" size="sm" /><span class="min-w-0 flex-1"><span class="block text-sm font-semibold truncate">{{ $u->name }}</span><span class="block text-xs text-muted">{{ $u->completedDeliveriesCount() }} pengantaran</span></span><x-ui.rating :value="$u->ratingAverage()" :show-empty="false" /></a>
                    @endforeach
                </div>
            </x-ui.card>
            <x-ui.card title="Audit trail terbaru" icon="history" :padding="false">
                <x-slot:actions><a href="{{ route('admin.audit-logs.index') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="divide-y divide-border">
                    @forelse ($recentEvents as $event)
                        <div class="p-3 text-xs"><a href="{{ route('admin.orders.show', $event->order) }}" class="font-semibold text-primary">{{ $event->order->code }}</a> <span class="text-muted">&bull; {{ $event->actor?->shortName() ?? 'Sistem' }} &bull; {{ $event->created_at->diffForHumans() }}</span><p class="text-on-surface-variant mt-0.5 line-clamp-2">{{ $event->description }}</p></div>
                    @empty
                        <p class="p-4 text-sm text-muted">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
