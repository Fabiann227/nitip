<x-layouts.app title="Metode Pembayaran">
    <x-ui.page-header title="Pengaturan" subtitle="Kelola identitas, kontak, dan cara penitip membayar kamu." />
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">@include('settings.partials.nav')</div>
        <div class="lg:col-span-3 space-y-6">
            @if ($user->hasPaymentMethod())
                <x-ui.card class="p-4 flex items-center gap-3 border-primary/40 bg-primary-fixed/10">
                    <span class="w-11 h-11 rounded-xl bg-white shadow-sm text-primary flex items-center justify-center shrink-0"><span class="material-symbols-outlined">{{ $user->payment_type->icon() }}</span></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-muted">Tujuan transfer aktif</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $user->paymentSummary() }}</p>
                    </div>
                    @if ($user->hasQris())<a href="{{ route('users.qris', $user) }}" target="_blank" class="btn btn-xs btn-outline"><span class="material-symbols-outlined text-[16px]">qr_code_2</span>QRIS</a>@endif
                    <x-ui.confirm-form :action="route('settings.payment.destroy')" method="DELETE" title="Hapus metode pembayaran?" message="Kamu tidak bisa mengambil titipan atau membuka rute sampai metode pembayaran diatur lagi." confirm="Hapus" variant="danger" button-class="btn btn-xs btn-danger-soft" icon="delete">Hapus</x-ui.confirm-form>
                </x-ui.card>
            @else
                <x-ui.alert type="warning" icon="account_balance_wallet" title="Belum ada metode pembayaran">Atur GoPay, OVO, DANA, ShopeePay, rekening bank, atau QRIS agar kamu bisa menerima pembayaran sebagai relawan.</x-ui.alert>
            @endif

            <x-ui.card :title="$user->hasPaymentMethod() ? 'Ubah metode pembayaran' : 'Atur metode pembayaran'" subtitle="Penitip mentransfer langsung ke sini saat kamu menjadi relawan (Direct P2P)." icon="account_balance_wallet">
                <form method="POST" action="{{ route('settings.payment.update') }}" enctype="multipart/form-data" class="space-y-5" x-data="{ type: @js(old('type', $user->payment_type?->value ?? 'gopay')) }" novalidate>
                    @csrf @method('PUT')
                    <div>
                        <span class="label">Jenis metode</span>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            @foreach ($types as $type)
                                <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer text-center transition-colors" :class="type === '{{ $type->value }}' ? 'border-primary bg-primary-fixed/20' : 'border-border hover:bg-surface-low'">
                                    <input type="radio" name="type" value="{{ $type->value }}" x-model="type" class="sr-only">
                                    <span class="material-symbols-outlined text-primary">{{ $type->icon() }}</span>
                                    <span class="text-xs font-semibold">{{ $type->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-ui.field-error name="type" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div x-show="type === 'bank_transfer'" x-cloak>
                            <x-ui.input name="provider_name" label="Nama bank" :value="$user->payment_provider" placeholder="Contoh: BCA, Mandiri, BNI" maxlength="60" />
                        </div>
                        <div x-show="type !== 'qris'">
                            <x-ui.input name="account_number" label="Nomor rekening / e-wallet" :value="$user->payment_account_number" placeholder="Contoh: 081234567890" inputmode="numeric" maxlength="60" />
                        </div>
                        <x-ui.input name="account_name" label="Nama pemilik" :value="$user->payment_account_name ?? $user->name" required maxlength="100" />
                        <div x-show="type === 'qris'" x-cloak>
                            <x-ui.file-input name="qris_image" :label="$user->hasQris() ? 'Ganti gambar QRIS (opsional)' : 'Gambar kode QRIS'" accept="image/*" :max-mb="4" icon="qr_code_2" />
                        </div>
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit" icon="save">Simpan metode pembayaran</x-ui.button></div>
                </form>
            </x-ui.card>

            <x-ui.alert type="info" icon="shield">Nitip tidak pernah menahan dana. Nomor rekening/e-wallet hanya ditampilkan ke penitip pada pesanan yang kamu ambil, sesuai regulasi Bank Indonesia tentang <em>holding balance</em>.</x-ui.alert>
        </div>
    </div>
</x-layouts.app>
