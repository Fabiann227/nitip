<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Development accounts. All passwords are "password" (documented in README).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(['email' => 'admin@nitip.test'], [
            'campus' => null,
            'role' => UserRole::Admin,
            'name' => 'Admin Nitip',
            'nim' => null,
            'whatsapp_number' => '6281100000000',
            'email_verified_at' => now(),
            'password' => 'password',
            'bio' => 'Operator platform Nitip.',
        ]);

        // [email, name, nim, campus, whatsapp, payment_type, provider, account_number]
        $students = [
            ['willy@student.uph.edu', 'Willy Lengkong', '01082230001', 'UPH', '6281234000001', 'gopay', null, '081234000001'],
            ['andhika@student.uph.edu', 'Andhika Bayangkara', '01082230002', 'UPH', '6281234000002', 'bank_transfer', 'BCA', '8830012345'],
            ['farhan@student.uph.edu', 'Farhan Febian Nauval', '01082230003', 'UPH', '6281234000003', 'dana', null, '081234000003'],
            ['sisila@student.uph.edu', 'Sisila Gunung', '01082230004', 'UPH', '6281234000004', 'bank_transfer', 'Mandiri', '1400012345678'],
            ['nadia@student.uph.edu', 'Nadia Putri Anjani', '01082230005', 'UPH', '6281234000005', 'gopay', null, '081234000005'],
            ['raka@student.uph.edu', 'Raka Pratama', '01082230006', 'UPH', '6281234000006', 'shopeepay', null, '081234000006'],
            ['maya@student.uph.edu', 'Maya Lestari', '01082230007', 'UPH', '6281234000007', 'ovo', null, '081234000007'],
            ['faris@student.uph.edu', 'Faris Kurniawan', '01082230008', 'UPH', '6281234000008', 'bank_transfer', 'BNI', '0123456789'],
            ['sarah@ui.ac.id', 'Sarah Tanaka', '2106721980', 'UI', '6281234000009', 'gopay', null, '081234000009'],
            ['dimas@ui.ac.id', 'Dimas Rahardian', '2006521111', 'UI', '6281234000010', 'bank_transfer', 'BCA', '5550098765'],
        ];

        foreach ($students as [$email, $name, $nim, $campus, $wa, $type, $provider, $number]) {
            User::query()->updateOrCreate(['email' => $email], [
                'campus' => $campus,
                'role' => UserRole::Student,
                'name' => $name,
                'nim' => $nim,
                'whatsapp_number' => $wa,
                'email_verified_at' => now(),
                'password' => 'password',
                'bio' => fake()->randomElement([
                    'Sering di perpus, bisa bantu titip kopi!',
                    'Anak kos gedung D, jalan ke food court tiap siang.',
                    'Ngampus jam 8-16, searah gerbang utama.',
                    null,
                ]),
                'payment_type' => $type,
                'payment_provider' => $provider,
                'payment_account_number' => $number,
                'payment_account_name' => $name,
            ]);
        }

        // Unverified account (to test the OTP flow) and a suspended account.
        User::query()->updateOrCreate(['email' => 'belumverif@student.uph.edu'], [
            'campus' => 'UPH',
            'role' => UserRole::Student,
            'name' => 'Budi Belum Verifikasi',
            'nim' => '01082230099',
            'whatsapp_number' => '6281234000099',
            'email_verified_at' => null,
            'password' => 'password',
        ]);

        User::query()->updateOrCreate(['email' => 'suspended@student.uph.edu'], [
            'campus' => 'UPH',
            'role' => UserRole::Student,
            'name' => 'Akun Ditangguhkan',
            'nim' => '01082230098',
            'whatsapp_number' => '6281234000098',
            'email_verified_at' => now(),
            'password' => 'password',
            'is_suspended' => true,
            'suspended_at' => now()->subDay(),
            'suspension_reason' => 'Berulang kali membatalkan pesanan setelah pembayaran.',
        ]);
    }
}
