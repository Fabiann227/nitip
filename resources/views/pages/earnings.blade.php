<x-layouts.app title="Penghasilan" wide>
    <x-ui.page-header eyebrow="Ekonomi mikro kampus" title="Penghasilan relawan" subtitle="Uang saku dari biaya jasa titipan yang kamu selesaikan. Dibayar langsung oleh penitip, tanpa potongan." />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-ui.stat-card label="Total diterima" :value="rupiah($stats['total'])" icon="savings" :hint="$stats['count'].' pengantaran selesai'" />
        <x-ui.stat-card label="Bulan ini" :value="rupiah($stats['month'])" icon="calendar_month" tone="secondary" :hint="$stats['month_count'].' pengantaran'" />
        <x-ui.stat-card label="Sedang berjalan" :value="rupiah($stats['pending'])" icon="hourglass_top" tone="warning" hint="biaya jasa titipan aktif" />
        <x-ui.stat-card label="Rating relawan" :value="$stats['rating'] ? number_format($stats['rating'], 1).' / 5' : '-'" icon="star" tone="info" :hint="$stats['rating_count'].' ulasan'" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="6 bulan terakhir" subtitle="Biaya jasa yang diterima per bulan" icon="bar_chart">
                <div class="flex items-end gap-2 sm:gap-4 h-44" role="img" aria-label="Grafik penghasilan bulanan">
                    @foreach ($monthly as $m)
                        <div class="flex-1 flex flex-col items-center gap-1 min-w-0">
                            <span class="text-[10px] sm:text-xs font-semibold text-on-surface tabular truncate">{{ $m['total'] > 0 ? rupiah($m['total'], false) : '' }}</span>
                            <div class="w-full rounded-t-lg bg-primary/90 transition-all" style="height: {{ max(4, round($m['total'] / $maxMonthly * 120)) }}px" title="{{ $m['label'] }}: {{ rupiah($m['total']) }} ({{ $m['count'] }} titipan)"></div>
                            <span class="text-[11px] text-muted">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Riwayat pengantaran selesai" icon="task_alt" :padding="false">
                <div class="p-3 space-y-2">
                    @forelse ($recent as $order)
                        <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-surface-low">
                            <x-ui.avatar :user="$order->requester" size="sm" />
                            <div class="min-w-0 flex-1"><p class="text-sm font-semibold truncate">{{ $order->title }}</p><p class="text-xs text-on-surface-variant">{{ $order->requester->shortName() }} &bull; {{ $order->completed_at?->translatedFormat('d M Y H:i') }}</p></div>
                            <span class="font-bold text-primary tabular text-sm">+{{ rupiah($order->service_fee) }}</span>
                        </a>
                    @empty
                        <x-ui.empty-state compact icon="directions_walk" title="Belum ada pengantaran selesai" description="Ambil titipan yang searah jalanmu untuk mulai mendapat uang saku.">
                            <x-ui.button :href="route('explore')" size="sm">Cari titipan</x-ui.button>
                        </x-ui.empty-state>
                    @endforelse
                </div>
                @if ($recent->hasPages())<div class="px-4 pb-4">{{ $recent->links() }}</div>@endif
            </x-ui.card>
        </div>
        <aside class="space-y-6">
            <x-ui.card title="Ulasan terbaru" icon="reviews">
                @forelse ($reviews as $review)
                    <div class="py-3 first:pt-0 last:pb-0 border-b last:border-b-0 border-border">
                        <div class="flex items-center justify-between gap-2"><x-ui.user-chip :user="$review->reviewer" size="xs" :subtitle="null" /><x-ui.rating :value="$review->rating" /></div>
                        @if ($review->comment)<p class="text-sm text-on-surface-variant mt-1">"{{ $review->comment }}"</p>@endif
                        <p class="text-[11px] text-muted mt-1">{{ $review->order?->title }} &bull; {{ $review->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-muted">Belum ada ulasan.</p>
                @endforelse
            </x-ui.card>
            <x-ui.card class="p-4 bg-secondary-container border-secondary-fixed">
                <p class="text-sm font-semibold text-on-secondary-container mb-1"><span class="material-symbols-outlined text-[18px] align-middle">eco</span> Tanpa komisi</p>
                <p class="text-xs text-on-secondary-container">Nitip tidak memotong biaya jasa. Dana langsung masuk ke e-wallet/rekeningmu dari penitip sesuai regulasi (tanpa saldo tertahan).</p>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
