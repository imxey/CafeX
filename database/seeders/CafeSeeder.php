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
                'created_at' => Carbon::parse('2025-05-26 08:11:09'),
                'updated_at' => Carbon::parse('2025-05-26 08:11:09'),
            ],
        ]);
    }
}
