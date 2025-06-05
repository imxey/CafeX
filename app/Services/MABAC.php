<?php

namespace App\Services;

use App\Models\Preferences;
use Illuminate\Support\Facades\DB;

class MABAC
{
    protected function hitungJarakHaversine(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB, float $earthRadius = 6371.0): float
    {
        $latA_rad = deg2rad($latitudeA);
        $lonA_rad = deg2rad($longitudeA);
        $latB_rad = deg2rad($latitudeB);
        $lonB_rad = deg2rad($longitudeB);

        $deltaLat = $latB_rad - $latA_rad;
        $deltaLon = $lonB_rad - $lonA_rad;

        $a = pow(sin($deltaLat / 2), 2) + cos($latA_rad) * cos($latB_rad) * pow(sin($deltaLon / 2), 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getDistance(int $userId): array
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user || $user->longitude === null || $user->latitude === null) {
            return [];
        }

        $userLongitude = (float) $user->longitude;
        $userLatitude = (float) $user->latitude;

        $cafes = DB::table('cafes')->select('id', 'name', 'latitude', 'longitude')
            ->whereNotNull('latitude')->whereNotNull('longitude')->get();

        $distances = [];
        foreach ($cafes as $cafe) {
            $distance = $this->hitungJarakHaversine($userLatitude, $userLongitude, (float) $cafe->latitude, (float) $cafe->longitude);
            $distances[] = ['cafe_name' => $cafe->name, round($distance, 2)];
        }

        return $distances;
    }

    public function calculate(int $userId, ?int $preferenceId = null): array
    {
        // 1. Ambil data preferensi
        $preferenceQuery = Preferences::where('user_id', $userId); // Gunakan Model Preferences
        if ($preferenceId !== null) {
            $userWeightData = $preferenceQuery->where('id', $preferenceId)->first();
        } else {
            $userWeightData = $preferenceQuery->latest('created_at')->first();
        }

        if (!$userWeightData) {
            // throw new \Exception("Preference data not found for user_id: {$userId}" . ($preferenceId ? " and preference_id: {$preferenceId}" : " (latest)"));
            return ['error' => "Preference data not found for user_id: {$userId}" . ($preferenceId ? " and preference_id: {$preferenceId}" : " (latest)")];
        }

        // 2. Ambil data alternatif (cafe)
        $alternativesData = DB::table('cafes')
            ->select('id', 'name', 'menu', 'price', 'wifi_speed', 'mosque')
            ->get();

        if ($alternativesData->isEmpty()) {
            return ['error' => "No alternative (cafe) data found."];
        }

        // 3. Hitung jarak
        $cafeDistances = $this->getDistance($userId);
        $distanceLookup = collect($cafeDistances)->keyBy('cafe_name');
        $alternativesData->each(function ($alt) use ($distanceLookup) {
            $alt->distance = $distanceLookup->get($alt->name)['distance'] ?? 9999; // Default jika tidak ketemu
        });

        // 4. Buat matriks keputusan
        $matrix = $alternativesData->map(function ($alt) {
            return [$alt->menu, $alt->price, $alt->wifi_speed, $alt->mosque, $alt->distance];
        })->all();

        if (empty($matrix)) {
            return ['error' => "Decision matrix could not be formed."];
        }

        // 5. Gunakan bobot dari preferensi
        $rawWeights = [
            $userWeightData->preference_menu,
            $userWeightData->preference_price,
            $userWeightData->preference_wifi_speed,
            $userWeightData->preference_mosque,
            $userWeightData->preference_distance,
        ];

        $sumRawWeights = array_sum($rawWeights);
        $weights = [];
        if ($sumRawWeights > 0) {
            $weights = array_map(fn($w) => $w / $sumRawWeights, $rawWeights);
        } else if (count($rawWeights) > 0) {
            $weights = array_fill(0, count($rawWeights), 1 / count($rawWeights));
        } else {
            return ['error' => "No criteria weights available from preference."];
        }

        $costBenefit = [1, 0, 1, 1, 0];

        // 6. Lakukan kalkulasi MABAC
        $normalized = $this->normalize($matrix, $costBenefit);
        $weighted = $this->applyWeight($normalized, $weights);
        $border = $this->borderApproximation($weighted);
        $distanceToBorder = $this->distanceToBorder($weighted, $border);

        $cafeDataForRanking = $alternativesData->map(function ($alt) {
            return ['cafe_id' => $alt->id, 'cafe_name' => $alt->name];
        })->all();
        $ranking = $this->rankingScore($distanceToBorder, $cafeDataForRanking);

        return [
            'matrix' => $matrix,
            'raw_weights' => $rawWeights,
            'normalized_weights' => $weights,
            'normalized_matrix' => $normalized,
            'weighted_matrix' => $weighted,
            'border_approximation' => $border,
            'distance_to_border' => $distanceToBorder,
            'ranking' => $ranking,
            'preference_created_at' => $userWeightData->created_at->toDateTimeString(),
        ];
    }

    protected function normalize($matrix, $costBenefit)
    {
        $norm = [];
        if (empty($matrix) || empty($matrix[0])) return $norm;

        $colCount = count($matrix[0]);
        for ($j = 0; $j < $colCount; $j++) {
            $col = array_column($matrix, $j);
            $min = min($col);
            $max = max($col);

            foreach ($matrix as $i => $row) {
                $norm[$i][$j] = ($max == $min) ? 0
                    : ($costBenefit[$j] == 1
                        ? ($row[$j] - $min) / ($max - $min)
                        : ($max - $row[$j]) / ($max - $min));
            }
        }
        return $norm;
    }

    protected function applyWeight($normalized, $weights)
    {
        return array_map(function ($row) use ($weights) {
            return array_map(fn($value, $weight) => $value * $weight, $row, $weights);
        }, $normalized);
    }

    protected function borderApproximation($weighted)
    {
        $border = [];
        $colCount = count($weighted[0]);
        $rowCount = count($weighted);

        for ($j = 0; $j < $colCount; $j++) {
            $product = 1;
            foreach ($weighted as $row) {
                $value = max(0.0001, $row[$j]);
                $product *= $value;
            }
            $border[$j] = pow($product, 1 / $rowCount);
        }

        return $border;
    }

    protected function distanceToBorder($weighted, $border)
    {
        return array_map(function ($row) use ($border) {
            return array_map(fn($val, $b) => $val - $b, $row, $border);
        }, $weighted);
    }

    protected function rankingScore(array $distanceToBorder, array $distances)
    {
        $results = [];
        foreach ($distances as $key => $item) {
            $results[] = [
                'cafe_name' => $item['cafe_name'] ?? '',
                'score'     => isset($distanceToBorder[$key])
                    ? array_sum($distanceToBorder[$key])
                    : 0
            ];
        }

        $sorted = $results;
        usort($sorted, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'score' => $results,
            'sorted_names' => array_column($sorted, 'cafe_name'),
        ];
    }
}
