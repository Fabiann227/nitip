<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email verification (OTP)
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'ttl_minutes' => 10,
        'resend_cooldown_seconds' => 60,
        'max_attempts' => 5,
        // Show the OTP on the verification page. Only honoured when APP_ENV=local.
        'dev_show' => (bool) env('NITIP_DEV_SHOW_OTP', false),
        // Accept ANY 6-digit code (dummy verification) while the mail setup is not ready.
        // Only honoured when APP_ENV=local or testing; never enable in production.
        'bypass' => (bool) env('NITIP_OTP_BYPASS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Campuses (master data kept in config to keep the database small)
    |--------------------------------------------------------------------------
    | Key = short code stored in users.campus / orders.campus / trips.campus.
    | "domains": campus email domains accepted besides the generic .ac.id suffix.
    | "locations": suggestions shown when typing pickup / drop-off locations.
    */
    'campus_email_suffixes' => ['.ac.id'],

    'campuses' => [
        'UPH' => [
            'name' => 'Universitas Pelita Harapan',
            'city' => 'Tangerang',
            'domains' => ['uph.edu', 'student.uph.edu'],
            'locations' => [
                'Food Court UPH (Gedung B Lt. 1)', 'Kantin Gedung D', 'Kantin Gedung F (Fakultas Kedokteran)',
                'Gedung B Lt. 3', 'Gedung C (Lab Komputer)', 'Gedung D Lt. 5', 'Gedung F Lt. 2',
                'Perpustakaan Johannes Oentoro', 'Fotokopi Gedung B Lt. 1', 'Print Center Perpustakaan',
                'Gerbang Utama (Lippo Village)', "McDonald's Lippo Karawaci", 'Kopi Kenangan Supermal Karawaci',
                'Asrama Mahasiswa (Lobby)', 'Kos Boulevard Diponegoro',
            ],
        ],
        'UI' => [
            'name' => 'Universitas Indonesia',
            'city' => 'Depok',
            'domains' => ['ui.ac.id'],
            'locations' => ['Kantin Dallas FIB', 'Kantin Vokasi', 'Fasilkom Central', 'Perpustakaan Pusat UI', 'Fotokopi Pusgiwa', 'Halte Bikun Stasiun UI', 'Asrama UI (Lobby)'],
        ],
        'ITB' => [
            'name' => 'Institut Teknologi Bandung',
            'city' => 'Bandung',
            'domains' => ['itb.ac.id'],
            'locations' => ['Kantin Barat', 'Kantin Bengkok', 'Labtek V', 'Perpustakaan Pusat ITB', 'Fotokopi Gerbang Ganesha', 'Gerbang Ganesha'],
        ],
        'UGM' => [
            'name' => 'Universitas Gadjah Mada',
            'city' => 'Yogyakarta',
            'domains' => ['ugm.ac.id', 'mail.ugm.ac.id'],
            'locations' => ['Kantin Bonbin', 'Gelanggang Mahasiswa', 'Perpustakaan Pusat UGM', 'Fotokopi Bulaksumur', 'Bundaran UGM'],
        ],
        'ITS' => [
            'name' => 'Institut Teknologi Sepuluh Nopember',
            'city' => 'Surabaya',
            'domains' => ['its.ac.id'],
            'locations' => ['Kantin Pusat ITS', 'Perpustakaan ITS', 'Gerbang Sukolilo'],
        ],
        'UNPAD' => [
            'name' => 'Universitas Padjadjaran',
            'city' => 'Sumedang',
            'domains' => ['unpad.ac.id'],
            'locations' => ['Kantin Gedung 2', 'Perpustakaan Unpad Jatinangor'],
        ],
        'BINUS' => [
            'name' => 'Universitas Bina Nusantara',
            'city' => 'Jakarta',
            'domains' => ['binus.ac.id', 'binus.edu'],
            'locations' => ['Kantin Kampus Anggrek', 'Library & Knowledge Center'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads (kilobytes)
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'image_max_kb' => 4096,
        'document_max_kb' => 10240,
        'image_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'document_mimes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'private_disk' => 'local',
        'public_disk' => 'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orders & trips
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'max_items' => 15,
        'min_service_fee' => 1000,
        'max_service_fee' => 50000,
        'max_item_cost' => 2000000,
    ],

    'trips' => [
        'max_slots' => 10,
        // Trips stop accepting new orders this many minutes before departure.
        'close_before_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp deep-link template
    |--------------------------------------------------------------------------
    */
    'whatsapp_template' => 'Halo :name, saya :sender dari Nitip. Terkait pesanan #:code (:title). ',
];
