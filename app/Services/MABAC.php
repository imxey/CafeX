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

        // Default to PNJ coordinates
        $userLatitude = -6.370504297709936;
        $userLongitude = 106.82359549354105;

        if ($user && $user->latitude !== null && $user->longitude !== null) {
            $userLatitude = (float) $user->latitude;
            $userLongitude = (float) $user->longitude;
        }

        $cafes = DB::table('cafes')->select('id', 'name', 'latitude', 'longitude')
            ->whereNotNull('latitude')->whereNotNull('longitude')->get();

        if ($cafes->isEmpty()) {
            return []; // No cafes with coordinates
        }

        $distances = [];
        foreach ($cafes as $cafe) {
            if (is_numeric($cafe->latitude) && is_numeric($cafe->longitude)) {
                $distance = $this->hitungJarakHaversine($userLatitude, $userLongitude, (float) $cafe->latitude, (float) $cafe->longitude);
                $distances[] = ['cafe_name' => $cafe->name, 'distance' => round($distance, 2)];
            } else {
                // Cafe has null or non-numeric lat/lon, assign a very large distance or log
                $distances[] = ['cafe_name' => $cafe->name, 'distance' => 99999.0];
            }
        }
        return $distances;
    }

    public function calculate(int $userId, ?int $preferenceId = null): array
    {
        // 1. Ambil data preferensi
        $preferenceQuery = Preferences::where('user_id', $userId);
        if ($preferenceId !== null) {
            $userWeightData = $preferenceQuery->where('id', $preferenceId)->first();
        } else {
            $userWeightData = $preferenceQuery->latest('created_at')->first();
        }

        if (!$userWeightData) {
            return ['error' => "Preference data not found for user_id: {$userId}" . ($preferenceId ? " and preference_id: {$preferenceId}" : " (latest)")];
        }

        // 2. Ambil data alternatif (cafe)
        $alternativesData = DB::table('cafes')
            ->select('id', 'name', 'menu', 'price', 'wifi_speed', 'mosque', 'latitude', 'longitude')
            ->get();

        if ($alternativesData->isEmpty()) {
            return ['error' => "No alternative (cafe) data found."];
        }

        // 3. Hitung jarak
        $cafeDistances = $this->getDistance($userId);
        $distanceLookup = collect($cafeDistances)->keyBy('cafe_name');

        $alternativesData->each(function ($alt) use ($distanceLookup) {
            $distanceInfo = $distanceLookup->get($alt->name);
            $alt->distance = $distanceInfo ? $distanceInfo['distance'] : 9999.0;
        });

        // 4. Buat matriks keputusan
        $matrix = $alternativesData->map(function ($alt) {
            return [
                (float) $alt->menu,
                (float) $alt->price,
                (float) $alt->wifi_speed,
                (float) $alt->mosque,
                (float) $alt->distance
            ];
        })->all();

        if (empty($matrix)) {
            return ['error' => "Decision matrix could not be formed."];
        }

        $rawWeights = [
            (float) $userWeightData->preference_menu,
            (float) $userWeightData->preference_price,
            (float) $userWeightData->preference_wifi_speed,
            (float) $userWeightData->preference_mosque,
            (float) $userWeightData->preference_distance,
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

        $normalized = $this->normalize($matrix, $costBenefit);
        $weighted = $this->applyWeight($normalized, $weights);
        $border = $this->borderApproximation($weighted);
        $distanceToBorder = $this->distanceToBorder($weighted, $border);

        $cafeIdentifiersForRanking = $alternativesData->map(function ($alt) {
            return ['cafe_id' => $alt->id, 'cafe_name' => $alt->name];
        })->all();
        $ranking = $this->rankingScore($distanceToBorder, $cafeIdentifiersForRanking);

        return [
            'requested_user_id' => $userId,
            'matrix' => $matrix,
            'cost_benefit_applied' => $costBenefit,
            'raw_weights' => $rawWeights,
            'normalized_weights_used' => $weights,
            'normalized_matrix' => $normalized,
            'weighted_matrix' => $weighted,
            'border_approximation' => $border,
            'distance_to_border' => $distanceToBorder,
            'ranking_score' => $ranking,
            'preference_created_at' => $userWeightData->created_at->toDateTimeString(),
        ];
    }

    protected function normalize($matrix, $costBenefit)
    {
        $norm = [];
        if (empty($matrix) || empty($matrix[0]))
            return $norm;

        $colCount = count($matrix[0]);
        for ($j = 0; $j < $colCount; $j++) {
            $col = array_column($matrix, $j);
            if (empty($col)) {
                foreach ($matrix as $i => $_) {
                    $norm[$i][$j] = 0;
                }
                continue;
            }
            $min = min($col);
            $max = max($col);

            foreach ($matrix as $i => $row) {
                if ($max == $min) {
                    $norm[$i][$j] = 0;
                } else {
                    if ($costBenefit[$j] == 1) {
                        $norm[$i][$j] = ($row[$j] - $min) / ($max - $min);
                    } else {
                        $norm[$i][$j] = ($max - $row[$j]) / ($max - $min);
                    }
                }
            }
        }
        return $norm;
    }

    protected function applyWeight($normalized, $weights)
    {
        $weighted = [];
        foreach ($normalized as $i => $row) {
            foreach ($row as $j => $value) {
                $weighted[$i][$j] = isset($weights[$j]) ? ($value * $weights[$j]) : 0;
            }
        }
        return $weighted;
    }

    protected function borderApproximation($weighted)
    {
        $border = [];
        if (empty($weighted) || empty($weighted[0]))
            return $border;

        $colCount = count($weighted[0]);
        $rowCount = count($weighted);

        if ($rowCount == 0)
            return $border;

        for ($j = 0; $j < $colCount; $j++) {
            $product = 1.0;
            $actualValuesInProduct = 0;
            foreach ($weighted as $row) {
                if (isset($row[$j])) {
                    $value = (float) $row[$j];
                    $product *= ($value <= 0 ? 0.0001 : $value);
                    $actualValuesInProduct++;
                }
            }
            if ($actualValuesInProduct > 0) {
                $border[$j] = pow($product, 1.0 / $actualValuesInProduct);
            } else {
                $border[$j] = 0.0001;
            }
        }
        return $border;
    }

    protected function distanceToBorder($weighted, $border)
    {
        $distance = [];
        foreach ($weighted as $i => $row) {
            foreach ($row as $j => $value) {
                $distance[$i][$j] = $value - ($border[$j] ?? 0);
            }
        }
        return $distance;
    }

    // Sort Ranking Cafe
    protected function rankingScore(array $distanceToBorder, array $cafeIdentifiers)
    {
        $results = [];
        foreach ($cafeIdentifiers as $key => $item) {
            $results[] = [
                'cafe_id'   => $item['cafe_id'] ?? null, // ADDED cafe_id
                'cafe_name' => $item['cafe_name'] ?? 'Unknown Cafe',
                'score'     => isset($distanceToBorder[$key]) && is_array($distanceToBorder[$key])
                    ? array_sum($distanceToBorder[$key])
                    : 0
            ];
        }

        $sortedResults = $results;
        usort($sortedResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'score' => $results,
            'sorted_items' => $sortedResults,
            'sorted_names' => array_column($sortedResults, 'cafe_name'),
        ];
    }
}
