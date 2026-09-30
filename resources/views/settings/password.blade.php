<x-layouts.app title="Kata Sandi">
    <x-ui.page-header title="Pengaturan" subtitle="Kelola identitas, kontak, dan cara penitip membayar kamu." />
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">@include('settings.partials.nav')</div>
        <div class="lg:col-span-3">
            <x-ui.card title="Ganti kata sandi" subtitle="Gunakan minimal 8 karakter dengan huruf dan angka." icon="lock">
                <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4 max-w-md" novalidate>
                    @csrf @method('PUT')
                    <x-ui.password name="current_password" label="Kata sandi saat ini" placeholder="Kata sandi lama" autocomplete="current-password" />
                    <x-ui.password name="password" label="Kata sandi baru" autocomplete="new-password" />
                    <x-ui.password name="password_confirmation" label="Konfirmasi kata sandi baru" icon="lock_reset" placeholder="Ulangi kata sandi baru" autocomplete="new-password" />
                    <div class="flex justify-end"><x-ui.button type="submit" icon="save">Simpan kata sandi</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
