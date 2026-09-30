# Nitip — panduan untuk agen/kontributor

Aplikasi Laravel 13 (PHP 8.3) + Blade + Tailwind v4 + Alpine.js + MySQL. Bahasa UI: Indonesia.

## Skema (sengaja ringkas, 8 tabel aplikasi)

`users` (termasuk tujuan pembayaran `payment_*` dan OTP `otp_*`), `service_categories`, `trips`, `orders`
(item & spesifikasi cetak = JSON; berkas, bukti transfer, struk = kolom path; pembayaran = kolom `payment_*`),
`order_events` (audit trail), `reviews`, `disputes`, `notifications`. Kampus & lokasi ada di `config/nitip.php`
(`App\Support\Campuses`), bukan tabel.

## Aturan penting

- **Jangan mengubah status order langsung dari controller.** Semua transisi lewat
  `App\Services\OrderWorkflowService` (mencatat `order_events`, notifikasi, validasi tahap).
- Pembayaran lewat `PaymentService`, rute lewat `TripService`, sengketa lewat `DisputeService`.
- Otorisasi memakai Policy (`app/Policies`) + middleware alias `admin` dan `active` (`bootstrap/app.php`).
- Pelanggaran aturan bisnis dilempar sebagai `App\Exceptions\NitipException` → otomatis menjadi flash `error`.
- File privat (bukti transfer, struk, berkas cetak, bukti sengketa, QRIS) di disk `local`, disajikan lewat
  `OrderFileController`; jangan pernah menaruhnya di `public`.
- Komponen UI reusable ada di `resources/views/components/ui`; pakai itu daripada menulis markup baru.
- Modal memakai `<template x-teleport>`; layout dasar sudah membungkus body dalam `x-data` root. Jangan
  memindahkan modal ke luar root itu, karena Alpine hanya menginisialisasi elemen di bawah `x-data`.
- Design token (warna, radius, shadow) didefinisikan di `resources/css/app.css` (`@theme`), mengikuti
  `stitch_nitip_campus_logistics_platform/campus_courier/DESIGN.md`.

## Perintah

```bash
php artisan migrate:fresh --seed   # reset + data demo
php artisan test                   # SQLite in-memory
vendor/bin/pint                    # code style
npm run dev | npm run build        # aset Vite (hasil build sudah di-commit di public/build)
```

Detail requirement dan keputusan arsitektur: `docs/ANALISIS_REQUIREMENT.md`. Setup & akun demo: `README.md`.
