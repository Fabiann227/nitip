<x-layouts.admin :title="$user->name">
    <x-slot:actions>
        @if (! $user->isAdmin())
            @if ($user->isSuspended())
                <x-ui.confirm-form :action="route('admin.users.unsuspend', $user)" title="Cabut penangguhan?" message="Pengguna kembali bisa membuat dan mengambil titipan." confirm="Cabut penangguhan" button-class="btn btn-sm btn-primary" icon="lock_open">Cabut penangguhan</x-ui.confirm-form>
            @else
                <x-ui.confirm-form :action="route('admin.users.suspend', $user)" title="Tangguhkan akun?" message="Pengguna tidak bisa mengakses area aplikasi sampai penangguhan dicabut. Alasan dikirim ke pengguna." confirm="Tangguhkan" variant="danger" reason reason-label="Alasan penangguhan" button-class="btn btn-sm btn-danger-soft" icon="block">Tangguhkan</x-ui.confirm-form>
            @endif
            @if (! $user->hasVerifiedEmail())
                <x-ui.confirm-form :action="route('admin.users.verify', $user)" title="Verifikasi manual?" message="Gunakan hanya jika identitas mahasiswa sudah dicek (mis. KTM)." confirm="Verifikasi" button-class="btn btn-sm btn-outline" icon="verified">Verifikasi manual</x-ui.confirm-form>
            @endif
        @endif
    </x-slot:actions>

    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:text-primary mb-4"><span class="material-symbols-outlined text-[16px]">arrow_back</span>Daftar pengguna</a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="space-y-6">
            <x-ui.card class="p-5">
                <div class="flex items-center gap-4 mb-4"><x-ui.avatar :user="$user" size="xl" /><div class="min-w-0"><h2 class="font-sans font-bold text-xl">{{ $user->name }}</h2><p class="text-sm text-muted truncate">{{ $user->email }}</p><div class="flex flex-wrap gap-1 mt-1">@if ($user->isAdmin())<x-ui.badge variant="primary">Admin</x-ui.badge>@endif @if ($user->isSuspended())<x-ui.badge variant="danger" icon="block">Ditangguhkan</x-ui.badge>@elseif ($user->hasVerifiedEmail())<x-ui.badge variant="success" icon="verified">Terverifikasi</x-ui.badge>@else<x-ui.badge variant="warning">Belum verifikasi</x-ui.badge>@endif</div></div></div>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between gap-2"><dt class="text-muted">Kampus</dt><dd class="font-semibold text-right">{{ $user->campusName() }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-muted">NIM</dt><dd class="font-semibold">{{ $user->nim ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-muted">WhatsApp</dt><dd class="font-semibold">{{ $user->whatsappPretty() }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-muted">Bergabung</dt><dd class="font-semibold">{{ $user->created_at->translatedFormat('d M Y') }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-muted">Terakhir aktif</dt><dd class="font-semibold">{{ $user->last_seen_at?->diffForHumans() ?? '-' }}</dd></div>
                </dl>
                @if ($user->isSuspended())<div class="mt-4 p-3 rounded-xl bg-error-container text-on-error-container text-sm"><span class="font-semibold">Alasan penangguhan:</span> {{ $user->suspension_reason }}<span class="block text-xs mt-1">Sejak {{ $user->suspended_at?->translatedFormat('d M Y H:i') }}</span></div>@endif
            </x-ui.card>
            <x-ui.card title="Metode pembayaran" icon="account_balance_wallet">
                <p class="text-sm">{{ $user->paymentSummary() }}</p>
                @if ($user->hasQris())<a href="{{ route('users.qris', $user) }}" target="_blank" class="btn btn-xs btn-outline mt-2"><span class="material-symbols-outlined text-[16px]">qr_code_2</span>Lihat QRIS</a>@endif
            </x-ui.card>
        </div>
        <div class="xl:col-span-2 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <x-ui.stat-card label="Titipan dibuat" :value="$stats['requests']" icon="shopping_bag" />
                <x-ui.stat-card label="Pengantaran selesai" :value="$stats['deliveries']" icon="directions_walk" tone="secondary" />
                <x-ui.stat-card label="Uang saku" :value="rupiah($stats['earned'])" icon="savings" tone="info" />
                <x-ui.stat-card label="Rating" :value="$stats['rating'] ? number_format($stats['rating'], 1) : '-'" icon="star" tone="warning" :hint="$stats['rating_count'].' ulasan · '.$stats['cancelled'].' pembatalan'" />
            </div>
            <x-ui.card title="Pesanan terkait" icon="receipt_long" :padding="false">
                <div class="overflow-x-auto"><table class="table"><thead><tr><th>Kode</th><th>Titipan</th><th>Peran</th><th>Status</th><th>Waktu</th></tr></thead><tbody>
                    @forelse ($orders as $order)
                        <tr><td><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary">{{ $order->code }}</a></td><td class="max-w-[220px] truncate">{{ $order->title }}</td><td>{{ $order->requester_id === $user->id ? 'Penitip' : 'Relawan' }}</td><td><x-ui.status-badge :status="$order->status" :icon="false" class="h-5 px-2 text-[10px]" /></td><td class="text-xs text-muted whitespace-nowrap">{{ $order->created_at->translatedFormat('d M H:i') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-6">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody></table></div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
