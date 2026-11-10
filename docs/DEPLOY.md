# Deploy Nitip ke VPS

Kondisi terpasang (sejak 10 Nov 2026): **https://nitip.76-13-22-7.sslip.io**

## Topologi

| Komponen | Detail |
|---|---|
| Server | VPS Ubuntu 24.04 (`root@76.13.22.7`, kunci SSH `~/hendra`), 2 vCPU / 8 GB |
| Web server | Caddy (sudah ada di server, dipakai bersama aplikasi lain). Site block Nitip ditambahkan di `/etc/caddy/Caddyfile`; HTTPS otomatis via Let's Encrypt untuk `nitip.76-13-22-7.sslip.io` (sslip.io mengarahkan nama itu ke IP, tanpa perlu beli domain) |
| PHP | PHP-FPM 8.3 (`php8.3-fpm`, pool `www` dinaikkan ke `pm.max_children = 25`) + ekstensi mysql, mbstring, xml, curl, zip, gd, bcmath, intl |
| Database | MySQL 8 lokal, database `nitip`, user `nitip` (password acak, tersimpan hanya di `.env` server) |
| Kode | `/var/www/nitip`, diunggah dari laptop dengan `git archive HEAD \| ssh ... tar -x` (aset Vite `public/build` sudah ter-commit, jadi server tidak butuh Node) |
| Mode | `APP_ENV=production`, `APP_DEBUG=false`, `NITIP_OTP_BYPASS=false`, config/route/view cache aktif |

## Deploy ulang setelah ada perubahan kode

Dari folder project di laptop (commit dulu perubahanmu):

```bash
git archive --format=tar HEAD | ssh -i ~/hendra root@76.13.22.7 'tar -x -C /var/www/nitip'
ssh -i ~/hendra root@76.13.22.7 'cd /var/www/nitip \
  && COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-interaction \
  && php artisan migrate --force \
  && php artisan config:cache && php artisan route:cache && php artisan view:cache \
  && chown -R www-data:www-data storage bootstrap/cache'
```

Reset data demo di server: `php artisan migrate:fresh --seed --force` (menghapus semua data!).

## Email OTP via Gmail (langkah yang tersisa, dikerjakan manual)

`MAIL_HOST=smtp.gmail.com` dan `MAIL_PORT=587` sudah terisi; yang belum hanya akun pengirimnya.

1. Buka https://myaccount.google.com → Security → aktifkan **2-Step Verification** → menu **App passwords** → buat password aplikasi (16 huruf) untuk "Mail".
2. Masuk ke server dan edit `.env` (jangan taruh password ini di chat/repo):
   ```bash
   ssh -i ~/hendra root@76.13.22.7
   nano /var/www/nitip/.env
   #   MAIL_MAILER=smtp
   #   MAIL_USERNAME=emailkamu@gmail.com
   #   MAIL_PASSWORD="abcd efgh ijkl mnop"   <- App Password 16 huruf
   #   MAIL_FROM_ADDRESS="emailkamu@gmail.com"
   php -r '' # lalu:
   cd /var/www/nitip && php artisan config:cache
   ```
3. Uji kirim tanpa harus registrasi:
   ```bash
   php artisan tinker --execute="Illuminate\Support\Facades\Notification::route('mail','emailkamu@gmail.com')->notify(new App\Notifications\VerifyEmailOtp('123456', 10));"
   ```
   Cek inbox; kalau masuk, alur registrasi → OTP → verifikasi sudah hidup beneran.

Sebelum langkah ini selesai, `MAIL_MAILER` masih `log`: registrasi akun baru di server akan macet di halaman OTP (kodenya hanya tertulis di `storage/logs/laravel.log`). Akun demo seeder sudah terverifikasi, jadi demo login tetap jalan.

## Akun demo (password: `password`)

Sama seperti lokal: `admin@nitip.test`, `willy@student.uph.edu`, `nadia@student.uph.edu`, dll. (lihat README).

## QA / stress test

Skrip dan laporan di `qa/stress_test/`:

- `stress.py` — jalankan dari laptop: `python qa/stress_test/stress.py` (butuh paket `requests`). Skenario: landing 30 koneksi, browsing 40 user login, 60 POST titipan paralel, dan race 10 relawan merebut 1 titipan x 5 ronde.
- `setup_loadtest_users.php` — buat ulang 40 akun uji di server sebelum menjalankan skrip (akun ini ikut terhapus setiap `migrate:fresh`).
- `Laporan_Stress_Test_Nitip.docx` + `stress_results.json` — hasil pengujian 10 Nov 2026. Inti: klaim selalu tepat 1 pemenang (lockForUpdate), 60/60 posting sukses; titik terberat adalah query statistik landing page.

Setelah stress test, rapikan data: `php artisan migrate:fresh --seed --force` di server.
