<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CafeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cafes')->insert([
            [
                'id' => 1,
                'name' => 'Forji',
                'latitude' => '-6.3649368',
                'longitude' => '106.8211469',
                'menu' => 16,
                'price' => 23,
                'wifi_speed' => 0,
                'mosque' => 0,
                'maps' => 'https://maps.app.goo.gl/biN5grUQHWFj8QSC6',
                'address' => 'Jl. H. Amat No.21, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425',
                'image_url' => '/images/dashboard.png',
                'open_time' => '09:00',
                'close_time' => '22:00',
                'created_at' => Carbon::parse('2025-05-26 08:07:50'),
                'updated_at' => Carbon::parse('2025-05-26 08:07:50'),
            ],
            [
                'id' => 2,
                'name' => 'Kylau Coffee',
                'latitude' => '-6.368574',
                'longitude' => '106.818536',
                'menu' => 67,
                'price' => 31,
                'wifi_speed' => 0,
                'mosque' => 1,
                'maps' => 'https://maps.app.goo.gl/WhN9UpVcAeGahQAX7',
                'address' => 'Jl. K.H.M. Usman No.8, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425',
                'image_url' => '/images/dashboard.png',
                'open_time' => '10:00',
                'close_time' => '23:00',
                'created_at' => Carbon::parse('2025-05-26 08:09:27'),
                'updated_at' => Carbon::parse('2025-05-26 08:09:27'),
            ],
            [
                'id' => 3,
                'name' => 'Suar Coffee',
                'latitude' => '-6.3600321',
                'longitude' => '106.8211763',
                'menu' => 60,
                'price' => 33,
                'wifi_speed' => 0,
                'mosque' => 0,
                'maps' => 'https://maps.app.goo.gl/vRywS5hiwCd6AZ1B7',
                'address' => 'Jl. H. Asmawi No.106, Beji, Kecamatan Beji, Kota Depok, Jawa Barat 16425',
                'image_url' => '/images/dashboard.png',
                'open_time' => '08:00',
                'close_time' => '21:00',
                'created_at' => Carbon::parse('2025-05-26 08:10:02'),
                'updated_at' => Carbon::parse('2025-05-26 08:10:02'),
            ],
            [
                'id' => 4,
                'name' => 'Mostly',
                'latitude' => '-6.3774033',
                'longitude' => '106.8230117',
                'menu' => 74,
                'price' => 35,
                'wifi_speed' => 0,
                'mosque' => 1,
                'maps' => 'https://maps.app.goo.gl/tmnS4Np36czFPL1Z6',
                'address' => 'Jl. Taufiqurrahman No.57A, Beji Tim., Kecamatan Beji, Kota Depok, Jawa Barat 16422',
                'image_url' => '/images/dashboard.png',
                'open_time' => '08:00',
                'close_time' => '22:00',
                'created_at' => Carbon::parse('2025-05-26 08:10:38'),
                'updated_at' => Carbon::parse('2025-05-26 08:10:38'),
            ],
            [
                'id' => 5,
                'name' => 'Tomoro',
                'latitude' => '-6.3632769',
                'longitude' => '106.8286782',
                'menu' => 111,
                'price' => 34,
                'wifi_speed' => 0,
                'mosque' => 0,
                'maps' => 'https://maps.app.goo.gl/mJzm4vHo8dH9X8UC7',
                'address' => 'Jl. Margonda Raya No.27, Pondok Cina, Kecamatan Beji, Kota Depok, Jawa Barat 16424',
                'image_url' => '/images/dashboard.png',
                'open_time' => '07:00',
                'close_time' => '22:00',
                'created_at' => Carbon::parse('2025-05-26 08:11:09'),
                'updated_at' => Carbon::parse('2025-05-26 08:11:09'),
            ],
        ]);
    }
}
