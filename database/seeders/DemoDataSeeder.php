<?php

namespace Database\Seeders;

use App\Enums\DisputeReason;
use App\Enums\DisputeResolution;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use App\Services\AccountService;
use App\Services\DisputeService;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use App\Services\ReviewService;
use App\Services\TripService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Replays realistic order flows through the real services so that
 * events, payments, files, notifications and reviews are all consistent.
 *
 * All timestamps are computed from $this->now (the real clock at seed start) and
 * applied with Carbon::setTestNow() so the audit trail reads like real history.
 */
class DemoDataSeeder extends Seeder
{
    private OrderService $orders;

    private OrderWorkflowService $workflow;

    private PaymentService $payments;

    private TripService $trips;

    private DisputeService $disputes;

    private ReviewService $reviews;

    private AccountService $accounts;

    private CarbonImmutable $now;

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, ServiceCategory> */
    private array $cats = [];

    private string $tmpDir;

    public function run(): void
    {
        $this->orders = app(OrderService::class);
        $this->workflow = app(OrderWorkflowService::class);
        $this->payments = app(PaymentService::class);
        $this->trips = app(TripService::class);
        $this->disputes = app(DisputeService::class);
        $this->reviews = app(ReviewService::class);
        $this->accounts = app(AccountService::class);

        $this->now = CarbonImmutable::now();
        $this->tmpDir = storage_path('app/tmp-seed');
        File::ensureDirectoryExists($this->tmpDir);

        $this->users = User::query()->students()->get()->keyBy(fn (User $u) => Str::before($u->email, '@'))->all();
        $this->cats = ServiceCategory::query()->get()->keyBy('code')->all();

        try {
            $this->seedTrips();
            $this->seedRequestFlows();
            $this->seedTripFlows();
            $this->seedDisputes();
            $this->seedAdminActions();
        } finally {
            Carbon::setTestNow();
            File::deleteDirectory($this->tmpDir);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Trips (OFFER stream)
    |--------------------------------------------------------------------------
    */

    private function seedTrips(): void
    {
        $t = $this->now;

        $this->at($t->subHours(3));
        $this->trips->create($this->users['willy'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'destination' => 'Food Court UPH (Gedung B Lt. 1)',
            'waypoints' => 'Perpustakaan Johannes Oentoro → Gedung C',
            'departure_at' => $t->addMinutes(75),
            'transport_mode' => 'walk',
            'max_slots' => 5,
            'service_fee' => 3500,
            'notes' => 'Habis kelas jam 12 langsung ke food court. Bisa bawa max 5 titipan makanan.',
        ]);

        $this->at($t->subHours(2));
        $this->trips->create($this->users['farhan'], [
            'service_category_id' => $this->cats['food_off_campus']->id,
            'destination' => 'McDonald\'s Lippo Karawaci',
            'waypoints' => 'Gerbang Utama (Lippo Village)',
            'departure_at' => $t->addHours(3),
            'transport_mode' => 'motorcycle',
            'max_slots' => 4,
            'service_fee' => 6000,
            'notes' => 'Naik motor, bisa sekalian Kopi Kenangan Supermal.',
        ]);

        $this->at($t->subHour());
        $this->trips->create($this->users['sisila'], [
            'service_category_id' => $this->cats['print_copy']->id,
            'destination' => 'Print Center Perpustakaan',
            'waypoints' => null,
            'departure_at' => $t->addHours(5),
            'transport_mode' => 'walk',
            'max_slots' => 3,
            'service_fee' => 4000,
            'notes' => 'Mau print skripsi, sekalian bantu print/jilid kalian.',
        ]);

        $this->at($t->subMinutes(30));
        $this->trips->create($this->users['dimas'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'destination' => 'Kantin Vokasi',
            'waypoints' => 'Balairung → FIB',
            'departure_at' => $t->addMinutes(90),
            'transport_mode' => 'walk',
            'max_slots' => 5,
            'service_fee' => 3500,
            'notes' => 'Terima titip makanan & minuman.',
        ]);

        // Yesterday's trip, closed after completing orders (see seedTripFlows).
        $yesterday = $t->subDay()->setTime(10, 0);
        $this->at($yesterday);
        $trip = $this->trips->create($this->users['andhika'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'destination' => 'Kantin Gedung D',
            'waypoints' => null,
            'departure_at' => $yesterday->addHours(2),
            'transport_mode' => 'walk',
            'max_slots' => 3,
            'service_fee' => 3000,
            'notes' => 'Makan siang di kantin D.',
        ]);
        $trip->forceFill(['code' => 'TR-DEMO01'])->save();

        // A cancelled trip from two days ago.
        $twoDaysAgo = $t->subDays(2)->setTime(9, 0);
        $this->at($twoDaysAgo);
        $cancelled = $this->trips->create($this->users['raka'], [
            'service_category_id' => $this->cats['food_off_campus']->id,
            'destination' => 'Kopi Kenangan Supermal Karawaci',
            'waypoints' => null,
            'departure_at' => $twoDaysAgo->addHours(3),
            'transport_mode' => 'motorcycle',
            'max_slots' => 3,
            'service_fee' => 5500,
            'notes' => null,
        ]);
        $this->at($twoDaysAgo->addMinutes(90));
        $this->trips->cancel($cancelled, $this->users['raka'], 'Kelas mendadak diganti jadwal, tidak jadi keluar.');
    }

    /*
    |--------------------------------------------------------------------------
    | Requests (REQUEST stream) in every lifecycle stage
    |--------------------------------------------------------------------------
    */

    private function seedRequestFlows(): void
    {
        $t = $this->now;

        // Open requests waiting for a fulfiller.
        $this->at($t->subMinutes(40));
        $this->orders->createRequest($this->users['nadia'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'title' => '2x Ayam Geprek Level 2 + Es Teh',
            'notes' => 'Sambalnya dipisah ya, jangan terlalu pedas.',
            'pickup_location' => 'Food Court UPH (Gedung B Lt. 1)',
            'dropoff_location' => 'Gedung D Lt. 5 (Ruang 501)',
            'needed_by' => $t->addMinutes(75),
            'service_fee' => 3500,
            'items' => [
                ['name' => 'Ayam Geprek Level 2', 'quantity' => 2, 'estimated_price' => 18000, 'note' => 'Sambal dipisah'],
                ['name' => 'Es Teh Manis', 'quantity' => 2, 'estimated_price' => 5000, 'note' => null],
            ],
        ]);

        $this->at($t->subMinutes(25));
        $this->orders->createRequest($this->users['faris'], [
            'service_category_id' => $this->cats['print_copy']->id,
            'title' => 'Print Laporan Praktikum Jaringan (45 hal) + Jilid',
            'notes' => 'Deadline dikumpul jam 3 sore, tolong dijilid spiral.',
            'pickup_location' => 'Fotokopi Gedung B Lt. 1',
            'dropoff_location' => 'Gedung C (Lab Komputer)',
            'needed_by' => $t->addHours(3),
            'service_fee' => 4500,
            'estimated_item_cost' => 25000,
            'print' => ['pages' => 45, 'copies' => 1, 'is_color' => false, 'paper_size' => 'A4', 'binding' => 'spiral', 'instructions' => 'Cover pakai kertas buffalo biru.'],
        ], $this->fakeDocument('laporan-praktikum-jaringan.pdf'));

        $this->at($t->subMinutes(15));
        $this->orders->createRequest($this->users['maya'], [
            'service_category_id' => $this->cats['food_off_campus']->id,
            'title' => 'Kopi Susu Aren Kopi Kenangan (2 cup)',
            'notes' => 'Less sugar, less ice.',
            'pickup_location' => 'Kopi Kenangan Supermal Karawaci',
            'dropoff_location' => 'Perpustakaan Johannes Oentoro Lt. 2',
            'needed_by' => $t->addHours(2),
            'service_fee' => 6000,
            'items' => [
                ['name' => 'Kopi Susu Aren (Regular)', 'quantity' => 2, 'estimated_price' => 22000, 'note' => 'Less sugar, less ice'],
            ],
        ]);

        $this->at($t->subMinutes(8));
        $this->orders->createRequest($this->users['sarah'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'title' => 'Roti Bakar Coklat Keju + Teh Tarik',
            'notes' => null,
            'pickup_location' => 'Kantin Dallas FIB',
            'dropoff_location' => 'Perpustakaan Pusat UI Lt. 3',
            'needed_by' => $t->addMinutes(50),
            'service_fee' => 4000,
            'items' => [
                ['name' => 'Roti Bakar Coklat Keju', 'quantity' => 1, 'estimated_price' => 15000, 'note' => null],
                ['name' => 'Teh Tarik', 'quantity' => 1, 'estimated_price' => 8000, 'note' => 'Hangat'],
            ],
        ]);

        // Expired open request (needed 3 hours ago).
        $this->at($t->subHours(5));
        $this->orders->createRequest($this->users['raka'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'title' => 'Nasi Goreng Spesial Kantin D',
            'notes' => 'Buat sarapan sebelum kelas jam 8.',
            'pickup_location' => 'Kantin Gedung D',
            'dropoff_location' => 'Gedung D Lt. 5',
            'needed_by' => $t->subHours(3),
            'service_fee' => 3000,
            'items' => [['name' => 'Nasi Goreng Spesial', 'quantity' => 1, 'estimated_price' => 17000, 'note' => null]],
        ]);

        // Claimed -> awaiting payment.
        $this->at($t->subMinutes(50));
        $awaiting = $this->orders->createRequest($this->users['andhika'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'title' => 'Mie Ayam Bakso + Es Jeruk',
            'notes' => null,
            'pickup_location' => 'Kantin Gedung D',
            'dropoff_location' => 'Gedung B Lt. 3 (Ruang 305)',
            'needed_by' => $t->addMinutes(90),
            'service_fee' => 3500,
            'items' => [
                ['name' => 'Mie Ayam Bakso', 'quantity' => 1, 'estimated_price' => 16000, 'note' => null],
                ['name' => 'Es Jeruk', 'quantity' => 1, 'estimated_price' => 6000, 'note' => null],
            ],
        ]);
        $this->at($t->subMinutes(35));
        $this->workflow->claim($awaiting, $this->users['willy']);

        // Payment submitted, waiting verification.
        $this->at($t->subMinutes(70));
        $submitted = $this->orders->createRequest($this->users['nadia'], [
            'service_category_id' => $this->cats['print_copy']->id,
            'title' => 'Print Slide Presentasi Berwarna (20 hal)',
            'notes' => 'Kertas agak tebal kalau bisa.',
            'pickup_location' => 'Print Center Perpustakaan',
            'dropoff_location' => 'Gedung F Lt. 2',
            'needed_by' => $t->addHours(2),
            'service_fee' => 4000,
            'estimated_item_cost' => 30000,
            'print' => ['pages' => 20, 'copies' => 1, 'is_color' => true, 'paper_size' => 'A4', 'binding' => 'staple', 'instructions' => null],
        ], $this->fakeDocument('slide-presentasi.pdf'));
        $this->at($t->subMinutes(55));
        $this->workflow->claim($submitted, $this->users['sisila']);
        $this->at($t->subMinutes(45));
        $this->payments->submitProof($submitted->fresh(), $this->users['nadia'], $this->fakeImage('Bukti Transfer'), 'Sudah transfer via m-banking 12:10');

        // Paid / in progress / delivering / delivered.
        $this->requestFlow($t->subHours(2), 'farhan', 'willy', 'food_in_campus', 'Nasi Padang Rendang + Es Teh', 'Food Court UPH (Gedung B Lt. 1)', 'Gedung C (Lab Komputer)', [
            ['name' => 'Nasi Padang Rendang', 'quantity' => 1, 'estimated_price' => 25000, 'note' => 'Kuah banyak'],
            ['name' => 'Es Teh', 'quantity' => 1, 'estimated_price' => 5000, 'note' => null],
        ], upTo: OrderStatus::Paid);

        $this->requestFlow($t->subHours(2)->addMinutes(10), 'maya', 'andhika', 'food_in_campus', 'Soto Ayam + Nasi (2 porsi)', 'Kantin Gedung D', 'Gedung D Lt. 5', [
            ['name' => 'Soto Ayam + Nasi', 'quantity' => 2, 'estimated_price' => 15000, 'note' => null],
        ], upTo: OrderStatus::InProgress);

        $this->requestFlow($t->subHours(2)->addMinutes(20), 'raka', 'farhan', 'food_off_campus', 'McD Paket Ayam 2 pcs + Cola', 'McDonald\'s Lippo Karawaci', 'Asrama Mahasiswa (Lobby)', [
            ['name' => 'Paket Ayam 2 pcs', 'quantity' => 1, 'estimated_price' => 42000, 'note' => 'Spicy'],
            ['name' => 'Coca-Cola Medium', 'quantity' => 1, 'estimated_price' => 12000, 'note' => null],
        ], upTo: OrderStatus::Delivering, actualCost: 57000);

        $this->requestFlow($t->subHours(2)->addMinutes(30), 'faris', 'nadia', 'food_in_campus', 'Batagor + Es Cincau', 'Food Court UPH (Gedung B Lt. 1)', 'Perpustakaan Johannes Oentoro Lt. 1', [
            ['name' => 'Batagor', 'quantity' => 1, 'estimated_price' => 12000, 'note' => null],
            ['name' => 'Es Cincau', 'quantity' => 1, 'estimated_price' => 7000, 'note' => null],
        ], upTo: OrderStatus::Delivered, actualCost: 20000);

        // Completed history with reviews (several days, for stats & earnings chart).
        $history = [
            [7, 'willy', 'andhika', 'food_in_campus', 'Ayam Bakar + Es Teh', [['name' => 'Ayam Bakar', 'quantity' => 1, 'estimated_price' => 20000, 'note' => null], ['name' => 'Es Teh', 'quantity' => 1, 'estimated_price' => 5000, 'note' => null]], 25000, [5, 5]],
            [6, 'nadia', 'willy', 'food_in_campus', 'Nasi Ayam Penyet', [['name' => 'Nasi Ayam Penyet', 'quantity' => 1, 'estimated_price' => 18000, 'note' => 'Pedas']], 18000, [5, 4]],
            [5, 'sisila', 'farhan', 'food_off_campus', 'Kopi Kenangan Americano 2 cup', [['name' => 'Americano', 'quantity' => 2, 'estimated_price' => 18000, 'note' => null]], 36000, [4, 5]],
            [4, 'andhika', 'sisila', 'print_copy', 'Print Makalah Etika (30 hal)', [], 12000, [5, 5]],
            [3, 'maya', 'willy', 'food_in_campus', 'Bakso Urat + Es Jeruk', [['name' => 'Bakso Urat', 'quantity' => 1, 'estimated_price' => 15000, 'note' => null], ['name' => 'Es Jeruk', 'quantity' => 1, 'estimated_price' => 6000, 'note' => null]], 21000, [5, 5]],
            [2, 'raka', 'willy', 'food_in_campus', 'Nasi Goreng Kampung', [['name' => 'Nasi Goreng Kampung', 'quantity' => 1, 'estimated_price' => 17000, 'note' => null]], 19000, [4, 5]],
            [1, 'faris', 'farhan', 'food_off_campus', 'Burger King Whopper Jr', [['name' => 'Whopper Jr', 'quantity' => 1, 'estimated_price' => 35000, 'note' => null]], 35000, [5, 4]],
            [35, 'nadia', 'willy', 'food_in_campus', 'Nasi Uduk Komplit', [['name' => 'Nasi Uduk Komplit', 'quantity' => 1, 'estimated_price' => 16000, 'note' => null]], 16000, [5, 5]],
            [40, 'maya', 'farhan', 'food_off_campus', 'Kopi Kenangan Latte 2 cup', [['name' => 'Latte', 'quantity' => 2, 'estimated_price' => 24000, 'note' => null]], 48000, [4, 4]],
            [65, 'raka', 'willy', 'food_in_campus', 'Ketoprak + Es Teh', [['name' => 'Ketoprak', 'quantity' => 1, 'estimated_price' => 14000, 'note' => null], ['name' => 'Es Teh', 'quantity' => 1, 'estimated_price' => 5000, 'note' => null]], 19000, [5, 5]],
        ];

        foreach ($history as [$daysAgo, $requester, $fulfiller, $cat, $title, $items, $actual, [$rReq, $rFul]]) {
            $day = $t->subDays($daysAgo)->setTime(11, 0);
            $order = $this->requestFlow($day, $requester, $fulfiller, $cat, $title, 'Food Court UPH (Gedung B Lt. 1)', 'Gedung B Lt. 3', $items, upTo: OrderStatus::Completed, actualCost: $actual);
            $this->at($day->setTime(13, 5));
            $this->reviews->create($order->fresh(), $this->users[$requester], $rReq, fake()->randomElement(['Cepat banget, makasih!', 'Pesanan sesuai dan masih hangat.', 'Ramah dan komunikatif.', null]));
            $this->at($day->setTime(13, 10));
            $this->reviews->create($order->fresh(), $this->users[$fulfiller], $rFul, fake()->randomElement(['Penitipnya responsif, transfer cepat.', 'Titik serah terima jelas.', null]));
        }

        // Cancelled by requester before payment.
        $y = $t->subDay()->setTime(15, 0);
        $this->at($y);
        $cancelled = $this->orders->createRequest($this->users['sisila'], [
            'service_category_id' => $this->cats['food_in_campus']->id,
            'title' => 'Es Kopi Susu Kantin F',
            'notes' => null,
            'pickup_location' => 'Kantin Gedung F (Fakultas Kedokteran)',
            'dropoff_location' => 'Gedung F Lt. 2',
            'needed_by' => $y->addHour(),
            'service_fee' => 3000,
            'items' => [['name' => 'Es Kopi Susu', 'quantity' => 1, 'estimated_price' => 12000, 'note' => null]],
        ]);
        $this->at($y->addMinutes(20));
        $this->workflow->cancel($cancelled, $this->users['sisila'], 'Sudah dibelikan teman sekelas.');

        // Released by fulfiller then cancelled by requester.
        $d2 = $t->subDays(2)->setTime(12, 0);
        $this->at($d2);
        $released = $this->orders->createRequest($this->users['maya'], [
            'service_category_id' => $this->cats['food_off_campus']->id,
            'title' => 'Chatime Brown Sugar 2 cup',
            'notes' => null,
            'pickup_location' => 'Supermal Karawaci',
            'dropoff_location' => 'Gedung D Lt. 5',
            'needed_by' => $d2->addHours(2),
            'service_fee' => 6500,
            'items' => [['name' => 'Chatime Brown Sugar', 'quantity' => 2, 'estimated_price' => 28000, 'note' => 'Less ice']],
        ]);
        $this->at($d2->addMinutes(10));
        $this->workflow->claim($released, $this->users['raka']);
        $this->at($d2->addMinutes(40));
        $this->workflow->release($released->fresh(), $this->users['raka'], 'Hujan deras, tidak jadi keluar.');
        $this->at($d2->addMinutes(60));
        $this->workflow->cancel($released->fresh(), $this->users['maya'], 'Sudah tidak butuh.');
    }

    /*
    |--------------------------------------------------------------------------
    | Orders that joined trips (OFFER stream)
    |--------------------------------------------------------------------------
    */

    private function seedTripFlows(): void
    {
        $t = $this->now;

        $willyTrip = Trip::query()->where('fulfiller_id', $this->users['willy']->id)->where('status', 'open')->firstOrFail();
        $farhanTrip = Trip::query()->where('fulfiller_id', $this->users['farhan']->id)->where('status', 'open')->firstOrFail();
        $demoTrip = Trip::query()->where('code', 'TR-DEMO01')->firstOrFail();

        // Two joins on Willy's open trip (one paid, one awaiting payment).
        $this->at($t->subMinutes(115));
        $join1 = $this->orders->joinTrip($willyTrip, $this->users['nadia'], [
            'title' => 'Nasi Uduk + Telur Balado',
            'notes' => 'Tanpa sambal.',
            'pickup_location' => 'Food Court UPH (Gedung B Lt. 1)',
            'dropoff_location' => 'Gedung D Lt. 5 (Ruang 502)',
            'items' => [['name' => 'Nasi Uduk Telur Balado', 'quantity' => 1, 'estimated_price' => 15000, 'note' => 'Tanpa sambal']],
        ]);
        $this->at($t->subMinutes(105));
        $this->payments->submitProof($join1->fresh(), $this->users['nadia'], $this->fakeImage('Bukti GoPay'), null);
        $this->at($t->subMinutes(95));
        $this->payments->verify($join1->fresh(), $this->users['willy']);

        $this->at($t->subMinutes(50));
        $this->orders->joinTrip($willyTrip->fresh(), $this->users['maya'], [
            'title' => 'Siomay 10 pcs',
            'notes' => null,
            'pickup_location' => 'Food Court UPH (Gedung B Lt. 1)',
            'dropoff_location' => 'Perpustakaan Johannes Oentoro Lt. 2',
            'items' => [['name' => 'Siomay (porsi 10)', 'quantity' => 1, 'estimated_price' => 20000, 'note' => 'Bumbu kacang extra']],
        ]);

        // One join on Farhan's trip, awaiting payment.
        $this->at($t->subMinutes(45));
        $this->orders->joinTrip($farhanTrip, $this->users['andhika'], [
            'title' => 'McFlurry Oreo + French Fries L',
            'notes' => null,
            'pickup_location' => 'McDonald\'s Lippo Karawaci',
            'dropoff_location' => 'Gedung B Lt. 3',
            'items' => [
                ['name' => 'McFlurry Oreo', 'quantity' => 1, 'estimated_price' => 14000, 'note' => null],
                ['name' => 'French Fries Large', 'quantity' => 1, 'estimated_price' => 25000, 'note' => null],
            ],
        ]);

        // Yesterday's demo trip: two completed orders, then closed.
        foreach ([['sisila', 'Gado-gado + Kerupuk', 18000], ['faris', 'Ayam Geprek + Es Teh', 23000]] as $i => [$requester, $title, $actual]) {
            $base = $t->subDay()->setTime(10, 20)->addMinutes($i * 5);
            $this->at($base);
            $order = $this->orders->joinTrip($demoTrip->fresh(), $this->users[$requester], [
                'title' => $title,
                'notes' => null,
                'pickup_location' => 'Kantin Gedung D',
                'dropoff_location' => 'Gedung B Lt. 3',
                'items' => [['name' => $title, 'quantity' => 1, 'estimated_price' => $actual, 'note' => null]],
            ]);
            $this->at($base->addMinutes(10));
            $this->payments->submitProof($order->fresh(), $this->users[$requester], $this->fakeImage('Bukti Transfer'), null);
            $this->at($base->addMinutes(20));
            $this->payments->verify($order->fresh(), $this->users['andhika']);
            $this->at($base->addMinutes(100));
            $this->workflow->start($order->fresh(), $this->users['andhika']);
            $this->at($base->addMinutes(120));
            $this->workflow->startDelivering($order->fresh(), $this->users['andhika'], $actual, $this->fakeImage('Struk Kasir'));
            $this->at($base->addMinutes(135));
            $this->workflow->markDelivered($order->fresh(), $this->users['andhika']);
            $this->at($base->addMinutes(140));
            $this->workflow->complete($order->fresh(), $this->users[$requester]);
            $this->at($base->addMinutes(150));
            $this->reviews->create($order->fresh(), $this->users[$requester], 5, 'Mantap, makasih Andhika!');
        }
        $this->at($t->subDay()->setTime(13, 0));
        $this->trips->close($demoTrip->fresh(), $this->users['andhika']);
    }

    /*
    |--------------------------------------------------------------------------
    | Disputes
    |--------------------------------------------------------------------------
    */

    private function seedDisputes(): void
    {
        $t = $this->now;

        // Open dispute: receipt mismatch on a delivered order.
        $disputed = $this->requestFlow($t->subHours(6), 'sarah', 'dimas', 'food_in_campus', 'Nasi Ayam Kantin Vokasi (3 porsi)', 'Kantin Vokasi', 'Fasilkom Central', [
            ['name' => 'Nasi Ayam', 'quantity' => 3, 'estimated_price' => 15000, 'note' => null],
        ], upTo: OrderStatus::Delivered, actualCost: 60000);
        $this->at($t->subHours(5));
        $this->disputes->open($disputed->fresh(), $this->users['sarah'], DisputeReason::ReceiptMismatch,
            'Struk yang difoto menunjukkan Rp 48.000, tetapi biaya riil yang diinput Rp 60.000. Mohon dicek selisihnya.',
            $this->fakeImage('Bukti Sengketa'));

        // Resolved dispute: fulfiller unresponsive -> cancelled with refund.
        $d3 = $t->subDays(3)->setTime(9, 0);
        $resolved = $this->requestFlow($d3, 'nadia', 'raka', 'food_off_campus', 'Starbucks Caramel Macchiato', 'Supermal Karawaci', 'Gedung D Lt. 5', [
            ['name' => 'Caramel Macchiato Grande', 'quantity' => 1, 'estimated_price' => 55000, 'note' => null],
        ], upTo: OrderStatus::InProgress);
        $this->at($d3->setTime(12, 0));
        $dispute = $this->disputes->open($resolved->fresh(), $this->users['nadia'], DisputeReason::Unresponsive,
            'Sudah 3 jam sejak pembayaran diverifikasi, relawan tidak bisa dihubungi lewat WhatsApp.');
        $this->at($d3->setTime(16, 0));
        $admin = User::query()->admins()->firstOrFail();
        $this->disputes->resolve($dispute, $admin, DisputeResolution::CancelWithRefund,
            'Relawan tidak merespons setelah dihubungi admin. Pesanan dibatalkan, relawan wajib mengembalikan Rp 61.000 ke penitip.');
    }

    private function seedAdminActions(): void
    {
        $t = $this->now;
        $admin = User::query()->admins()->firstOrFail();
        $suspended = User::query()->where('email', 'suspended@student.uph.edu')->first();

        if ($suspended) {
            $this->at($t->subDay()->setTime(9, 30));
            $this->accounts->unsuspend($suspended, $admin);
            $this->at($t->subDay()->setTime(9, 35));
            $this->accounts->suspend($suspended, $admin, 'Berulang kali membatalkan pesanan setelah pembayaran.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Drive a request through the lifecycle up to a target status, each step 8 minutes apart.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function requestFlow(CarbonImmutable $start, string $requester, string $fulfiller, string $cat, string $title, string $pickup, string $dropoff, array $items, OrderStatus $upTo, ?int $actualCost = null): Order
    {
        $category = $this->cats[$cat];
        $isPrint = $category->isPrint();

        $this->at($start);

        $order = $this->orders->createRequest($this->users[$requester], [
            'service_category_id' => $category->id,
            'title' => $title,
            'notes' => null,
            'pickup_location' => $pickup,
            'dropoff_location' => $dropoff,
            'needed_by' => $start->addHours(3),
            'service_fee' => $category->fee_default,
            'items' => $items,
            'estimated_item_cost' => $isPrint ? ($actualCost ?? 10000) : 0,
            'print' => $isPrint ? ['pages' => 30, 'copies' => 1, 'is_color' => false, 'paper_size' => 'A4', 'binding' => 'staple'] : [],
        ], $isPrint ? $this->fakeDocument('makalah.pdf') : null);

        $estimated = $order->estimated_item_cost;

        $steps = [
            OrderStatus::AwaitingPayment->value => fn () => $this->workflow->claim($order->fresh(), $this->users[$fulfiller]),
            OrderStatus::PaymentSubmitted->value => fn () => $this->payments->submitProof($order->fresh(), $this->users[$requester], $this->fakeImage('Bukti Transfer'), null),
            OrderStatus::Paid->value => fn () => $this->payments->verify($order->fresh(), $this->users[$fulfiller]),
            OrderStatus::InProgress->value => fn () => $this->workflow->start($order->fresh(), $this->users[$fulfiller]),
            OrderStatus::Delivering->value => fn () => $this->workflow->startDelivering($order->fresh(), $this->users[$fulfiller], $actualCost ?? $estimated, $this->fakeImage('Struk Kasir')),
            OrderStatus::Delivered->value => fn () => $this->workflow->markDelivered($order->fresh(), $this->users[$fulfiller]),
            OrderStatus::Completed->value => fn () => $this->workflow->complete($order->fresh(), $this->users[$requester]),
        ];

        $i = 0;
        foreach ($steps as $status => $step) {
            $this->at($start->addMinutes(8 * (++$i)));
            $step();

            if ($status === $upTo->value) {
                break;
            }
        }

        return $order->fresh();
    }

    private function at(\DateTimeInterface $time): void
    {
        Carbon::setTestNow(Carbon::instance($time));
    }

    private function fakeImage(string $label): UploadedFile
    {
        $path = $this->tmpDir.'/'.Str::uuid().'.png';

        $image = imagecreatetruecolor(640, 900);
        $bg = imagecolorallocate($image, 248, 249, 255);
        $green = imagecolorallocate($image, 0, 96, 65);
        $dark = imagecolorallocate($image, 18, 28, 42);
        $grey = imagecolorallocate($image, 190, 201, 193);
        imagefill($image, 0, 0, $bg);
        imagefilledrectangle($image, 0, 0, 640, 110, $green);
        imagestring($image, 5, 24, 40, 'NITIP - '.strtoupper($label), $bg);
        imagestring($image, 4, 24, 150, 'Placeholder gambar untuk data demo.', $dark);
        imagestring($image, 3, 24, 190, 'Dibuat otomatis oleh DemoDataSeeder.', $dark);
        for ($y = 260; $y < 820; $y += 46) {
            imageline($image, 24, $y, 616, $y, $grey);
            imagestring($image, 3, 24, $y - 20, 'Item '.(($y - 260) / 46 + 1).' ........................................ Rp '.number_format(random_int(5000, 40000), 0, ',', '.'), $dark);
        }
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, Str::slug($label).'.png', 'image/png', null, true);
    }

    private function fakeDocument(string $name): UploadedFile
    {
        $path = $this->tmpDir.'/'.Str::uuid().'.pdf';

        $content = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n".
            "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n".
            "4 0 obj<</Length 60>>stream\nBT /F1 18 Tf 72 760 Td (Nitip demo document) Tj ET\nendstream\nendobj\n".
            "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";

        File::put($path, $content);

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    }
}
