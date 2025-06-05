<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dates = [
            Carbon::now()->subDays(3)->startOfDay(),
            Carbon::now()->subDays(2)->startOfDay(),
            Carbon::now()->subDays(1)->startOfDay(),
        ];

        foreach ($dates as $date) {
            for ($i = 0; $i < 3; $i++) {
                DB::table('preferences')->insert([
                    'user_id' => 1,
                    'created_at' => $date->copy()->addHours($i), // supaya jamnya beda
                    'preference_menu' => rand(1, 5),
                    'preference_price' => rand(1, 5),
                    'preference_wifi_speed' => rand(1, 5),
                    'preference_distance' => rand(1, 5),
                    'preference_mosque' => rand(1, 5),
                ]);
            }
        }
    }
}
