<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Route;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@pocan.com'],
            [
                'name' => 'Administrator CAN Travel',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'budi@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081298765432',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'siti@gmail.com'],
            [
                'name' => 'Siti Rahmawati',
                'phone' => '081377889900',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]
        );

        $customer3 = User::firstOrCreate(
            ['email' => 'ahmad@gmail.com'],
            [
                'name' => 'Ahmad Dahlan',
                'phone' => '085611223344',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed Buses & Seats
        $busesData = [
            [
                'name' => 'CAN Royal Suite 01',
                'code' => 'CAN-RS-01',
                'type' => 'Royal Suite',
                'seat_capacity' => 24,
                'facilities' => ['AC Dingin', 'WiFi Cepat', 'Reclining Seat 150°', 'Legrest Ergonomis', 'Audio Video on Demand (AVOD)', 'USB Type-C & A Fast Charger', 'Toilet Bersih', 'Snack & Air Mineral', 'Bantal & Selimut Lembut'],
                'description' => 'Armada flagship termewah dari CAN Travel dengan konfigurasi kursi 2-2 legrest ekstra luas, AVOD tiap kursi, dan layanan kru bintang lima.',
                'status' => 'active',
            ],
            [
                'name' => 'CAN Executive Grand 02',
                'code' => 'CAN-EX-02',
                'type' => 'Executive',
                'seat_capacity' => 28,
                'facilities' => ['AC Central', 'Free WiFi', 'Reclining Seat Nyaman', 'Legrest', 'Central TV LED', 'USB Port', 'Toilet', 'Snack Box', 'Selimut'],
                'description' => 'Bus Executive andalan dengan suspensi udara Scania K410IB yang senyap, cocok untuk perjalanan jauh lintas pulau.',
                'status' => 'active',
            ],
            [
                'name' => 'CAN Sleeper Dream 03',
                'code' => 'CAN-SL-03',
                'type' => 'Sleeper Bus',
                'seat_capacity' => 20,
                'facilities' => ['Full Flat Bed 180°', 'Personal Android Tablet TV', 'Ultra High Speed WiFi', 'Tirai Privasi', 'Dual USB Fast Charger', 'Toilet Higienis', 'Makanan Hangat & Kopi', 'Bantal & Selimut Premium'],
                'description' => 'Pengalaman tidur nyenyak sepanjang perjalanan dengan bilik kabin privat serasa hotel kapsul berjalan.',
                'status' => 'active',
            ],
            [
                'name' => 'CAN VIP Line 04',
                'code' => 'CAN-VIP-04',
                'type' => 'VIP',
                'seat_capacity' => 32,
                'facilities' => ['AC Sejuk', 'Reclining Seat', 'Audio Musik', 'USB Charger Tiap Baris', 'Air Mineral 600ml'],
                'description' => 'Pilihan ekonomis dan nyaman untuk perjalanan keluarga dan rombongan dengan kursi ergonomis.',
                'status' => 'active',
            ],
        ];

        $buses = [];
        foreach ($busesData as $bData) {
            $bus = Bus::updateOrCreate(['code' => $bData['code']], $bData);
            $bus->generateSeats($bData['seat_capacity']);
            $buses[$bData['code']] = $bus;
        }

        // 3. Seed Routes
        $routesData = [
            [
                'origin' => 'Jakarta (Terminal Pulo Gebang)',
                'destination' => 'Yogyakarta (Terminal Giwangan)',
                'distance' => '560 km',
                'estimated_duration' => '8 Jam 30 Menit',
                'base_price' => 260000,
                'status' => 'active',
            ],
            [
                'origin' => 'Yogyakarta (Terminal Giwangan)',
                'destination' => 'Jakarta (Terminal Pulo Gebang)',
                'distance' => '560 km',
                'estimated_duration' => '8 Jam 30 Menit',
                'base_price' => 260000,
                'status' => 'active',
            ],
            [
                'origin' => 'Jakarta (Terminal Kampung Rambutan)',
                'destination' => 'Surabaya (Terminal Purabaya Bungurasih)',
                'distance' => '780 km',
                'estimated_duration' => '10 Jam 15 Menit',
                'base_price' => 340000,
                'status' => 'active',
            ],
            [
                'origin' => 'Surabaya (Terminal Purabaya Bungurasih)',
                'destination' => 'Jakarta (Terminal Kampung Rambutan)',
                'distance' => '780 km',
                'estimated_duration' => '10 Jam 15 Menit',
                'base_price' => 340000,
                'status' => 'active',
            ],
            [
                'origin' => 'Bandung (Terminal Leuwipanjang)',
                'destination' => 'Solo (Terminal Tirtonadi)',
                'distance' => '490 km',
                'estimated_duration' => '7 Jam 45 Menit',
                'base_price' => 230000,
                'status' => 'active',
            ],
            [
                'origin' => 'Jakarta (Terminal Kalideres)',
                'destination' => 'Semarang (Terminal Terboyo/Mangkang)',
                'distance' => '440 km',
                'estimated_duration' => '6 Jam 20 Menit',
                'base_price' => 210000,
                'status' => 'active',
            ],
        ];

        $routes = [];
        foreach ($routesData as $rData) {
            $routes[] = Route::firstOrCreate(
                ['origin' => $rData['origin'], 'destination' => $rData['destination']],
                $rData
            );
        }

        // 4. Seed Trips (Schedules) for Today, Tomorrow, and Next Days
        $now = Carbon::now();
        $trips = [];

        $scheduleTemplates = [
            // Today
            ['day_offset' => 0, 'dep_hour' => 18, 'dep_min' => 30, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-RS-01', 'price' => 280000, 'code' => 'TRIP-JKTYOG-01'],
            ['day_offset' => 0, 'dep_hour' => 19, 'dep_min' => 45, 'duration_hours' => 10, 'route_idx' => 2, 'bus_code' => 'CAN-SL-03', 'price' => 380000, 'code' => 'TRIP-JKTSBY-01'],
            // Tomorrow (+1)
            ['day_offset' => 1, 'dep_hour' => 7, 'dep_min' => 30, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-EX-02', 'price' => 260000, 'code' => 'TRIP-JKTYOG-02'],
            ['day_offset' => 1, 'dep_hour' => 8, 'dep_min' => 0, 'duration_hours' => 6, 'route_idx' => 5, 'bus_code' => 'CAN-VIP-04', 'price' => 210000, 'code' => 'TRIP-JKTSMG-01'],
            ['day_offset' => 1, 'dep_hour' => 19, 'dep_min' => 0, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-RS-01', 'price' => 280000, 'code' => 'TRIP-JKTYOG-03'],
            ['day_offset' => 1, 'dep_hour' => 20, 'dep_min' => 30, 'duration_hours' => 10, 'route_idx' => 2, 'bus_code' => 'CAN-SL-03', 'price' => 380000, 'code' => 'TRIP-JKTSBY-02'],
            ['day_offset' => 1, 'dep_hour' => 15, 'dep_min' => 30, 'duration_hours' => 7, 'route_idx' => 4, 'bus_code' => 'CAN-EX-02', 'price' => 230000, 'code' => 'TRIP-BDGSOL-01'],
            // +2 Days
            ['day_offset' => 2, 'dep_hour' => 8, 'dep_min' => 30, 'duration_hours' => 8, 'route_idx' => 1, 'bus_code' => 'CAN-EX-02', 'price' => 260000, 'code' => 'TRIP-YOGJKT-01'],
            ['day_offset' => 2, 'dep_hour' => 19, 'dep_min' => 30, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-RS-01', 'price' => 280000, 'code' => 'TRIP-JKTYOG-04'],
            ['day_offset' => 2, 'dep_hour' => 20, 'dep_min' => 0, 'duration_hours' => 10, 'route_idx' => 3, 'bus_code' => 'CAN-SL-03', 'price' => 380000, 'code' => 'TRIP-SBYJKT-01'],
            // +3 Days
            ['day_offset' => 3, 'dep_hour' => 9, 'dep_min' => 0, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-EX-02', 'price' => 260000, 'code' => 'TRIP-JKTYOG-05'],
            ['day_offset' => 3, 'dep_hour' => 19, 'dep_min' => 0, 'duration_hours' => 8, 'route_idx' => 0, 'bus_code' => 'CAN-RS-01', 'price' => 280000, 'code' => 'TRIP-JKTYOG-06'],
        ];

        foreach ($scheduleTemplates as $idx => $st) {
            $depDate = $now->copy()->addDays($st['day_offset'])->setTime($st['dep_hour'], $st['dep_min'], 0);
            $arrDate = $depDate->copy()->addHours($st['duration_hours'])->addMinutes(15);
            $route = $routes[$st['route_idx']];
            $bus = $buses[$st['bus_code']];
            $uniqueCode = $st['code'].'-'.$depDate->format('ymd');

            $trip = Trip::updateOrCreate(
                ['trip_code' => $uniqueCode],
                [
                    'bus_id' => $bus->id,
                    'route_id' => $route->id,
                    'departure_at' => $depDate,
                    'arrival_at' => $arrDate,
                    'price' => $st['price'],
                    'boarding_point' => $route->origin,
                    'drop_off_point' => $route->destination,
                    'status' => 'scheduled',
                ]
            );
            $trips[] = $trip;
        }

        // 5. Seed Pre-existing Orders, Order Items & Payments
        // Order 1: Completed & Paid by Budi for trip 0 (Tomorrow Jakarta - Yogya)
        if (! empty($trips)) {
            $tripSample1 = $trips[2]; // tomorrow trip
            $seatsTrip1 = $tripSample1->bus->busSeats()->take(2)->get();

            $order1 = Order::updateOrCreate(
                ['order_code' => 'CAN-ORD-20260901-001'],
                [
                    'user_id' => $customer1->id,
                    'trip_id' => $tripSample1->id,
                    'total_amount' => $tripSample1->price * 2,
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'expires_at' => null,
                    'notes' => 'Tolong sediakan kursi bersebelahan.',
                ]
            );

            // Order items
            $order1->orderItems()->delete();
            $order1->orderItems()->create([
                'bus_seat_id' => $seatsTrip1[0]->id,
                'passenger_name' => 'Budi Santoso',
                'passenger_phone' => '081298765432',
                'passenger_id_number' => '3271012345670001',
                'price' => $tripSample1->price,
            ]);
            $order1->orderItems()->create([
                'bus_seat_id' => $seatsTrip1[1]->id,
                'passenger_name' => 'Anisa Santoso',
                'passenger_phone' => '081298765433',
                'passenger_id_number' => '3271012345670002',
                'price' => $tripSample1->price,
            ]);

            Payment::updateOrCreate(
                ['payment_reference' => 'PAY-CAN-20260901-001'],
                [
                    'order_id' => $order1->id,
                    'user_id' => $customer1->id,
                    'payment_method' => 'BCA Virtual Account',
                    'amount' => $order1->total_amount,
                    'status' => 'success',
                    'paid_at' => now()->subDay(),
                    'proof_file' => null,
                ]
            );

            // Order 2: Another order by Siti
            $tripSample2 = $trips[3]; // Semarang trip
            $seatsTrip2 = $tripSample2->bus->busSeats()->skip(4)->take(1)->get();
            if ($seatsTrip2->isNotEmpty()) {
                $order2 = Order::updateOrCreate(
                    ['order_code' => 'CAN-ORD-20260901-002'],
                    [
                        'user_id' => $customer2->id,
                        'trip_id' => $tripSample2->id,
                        'total_amount' => $tripSample2->price,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'expires_at' => null,
                        'notes' => 'Perjalanan bisnis.',
                    ]
                );

                $order2->orderItems()->delete();
                $order2->orderItems()->create([
                    'bus_seat_id' => $seatsTrip2[0]->id,
                    'passenger_name' => 'Siti Rahmawati',
                    'passenger_phone' => '081377889900',
                    'passenger_id_number' => '3374019876540003',
                    'price' => $tripSample2->price,
                ]);

                Payment::updateOrCreate(
                    ['payment_reference' => 'PAY-CAN-20260901-002'],
                    [
                        'order_id' => $order2->id,
                        'user_id' => $customer2->id,
                        'payment_method' => 'QRIS Instant Pay',
                        'amount' => $order2->total_amount,
                        'status' => 'success',
                        'paid_at' => now()->subHours(5),
                        'proof_file' => null,
                    ]
                );
            }
        }
    }
}
