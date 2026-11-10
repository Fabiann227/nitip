<?php

/**
 * Membuat 40 akun uji beban (loadtest01..40@student.uph.edu, password "password"),
 * terverifikasi dan punya metode pembayaran, untuk qa/stress_test/stress.py.
 *
 * Jalankan di server:
 *   cd /var/www/nitip
 *   php artisan tinker --execute="require 'qa/stress_test/setup_loadtest_users.php';"
 *
 * Catatan: email_verified_at dan payment_* sengaja di-update lewat query builder,
 * karena kolom-kolom itu tidak termasuk #[Fillable] di model User (mass-assignment
 * protection) — create()/updateOrCreate() akan membuangnya diam-diam.
 */

use App\Models\User;
use Illuminate\Support\Facades\DB;

for ($i = 1; $i <= 40; $i++) {
    User::query()->updateOrCreate(
        ['email' => sprintf('loadtest%02d@student.uph.edu', $i)],
        [
            'role' => 'student',
            'name' => sprintf('Load Tester %02d', $i),
            'nim' => sprintf('99990000%02d', $i),
            'campus' => 'UPH',
            'whatsapp_number' => sprintf('08120000%04d', $i),
            'password' => 'password',
        ],
    );
}

$n = DB::table('users')->where('email', 'like', 'loadtest%')->update([
    'email_verified_at' => now(),
    'payment_type' => 'gopay',
    'payment_provider' => 'GoPay',
    'payment_account_number' => '081200000000',
    'payment_account_name' => 'Load Tester',
]);

echo $n." akun loadtest siap\n";
