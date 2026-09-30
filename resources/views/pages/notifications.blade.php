<x-layouts.app title="Notifikasi">
    <x-ui.page-header title="Notifikasi" :subtitle="$unreadCount > 0 ? $unreadCount.' belum dibaca' : 'Semua sudah dibaca'">
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<x-ui.button type="submit" size="sm" variant="outline" icon="done_all">Tandai semua dibaca</x-ui.button></form>
        @endif
    </x-ui.page-header>

    @if ($notifications->isEmpty())
        <x-ui.card><x-ui.empty-state icon="notifications_off" title="Belum ada notifikasi" description="Kabar tentang titipan, pembayaran, dan ulasan akan muncul di sini." /></x-ui.card>
    @else
        <div class="card divide-y divide-border">
            @foreach ($notifications as $notification)
                @php $data = $notification->data; $tone = $data['tone'] ?? 'info'; @endphp
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-start gap-3 p-4 hover:bg-canvas transition-colors {{ $notification->read_at ? '' : 'bg-primary-fixed/10' }}">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ ['success' => 'badge-success', 'danger' => 'badge-danger', 'warning' => 'badge-warning'][$tone] ?? 'badge-info' }}"><span class="material-symbols-outlined text-[20px]">{{ $data['icon'] ?? 'notifications' }}</span></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2"><span class="text-sm font-semibold text-on-surface">{{ $data['title'] ?? 'Notifikasi' }}</span>@if (! $notification->read_at)<span class="w-2 h-2 rounded-full bg-primary"></span>@endif</span>
                            <span class="block text-sm text-on-surface-variant">{{ $data['message'] ?? '' }}</span>
                            <span class="block text-xs text-muted mt-0.5">{{ $notification->created_at->diffForHumans() }}@if (! empty($data['order_code'])) &bull; #{{ $data['order_code'] }}@endif</span>
                        </span>
                        @if (! empty($data['url']))<span class="material-symbols-outlined text-outline shrink-0">chevron_right</span>@endif
                    </button>
                </form>
            @endforeach
        </div>
        {{ $notifications->links() }}
    @endif
</x-layouts.app>
