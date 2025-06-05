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
            //set latitude and longitude in PNJ
            $user->longitude = 106.82359549354105;
            $user->latitude = -6.370504297709936;
        }

        $userLongitude = (float) $user->longitude;
        $userLatitude = (float) $user->latitude;

        $cafes = DB::table('cafes')->select('id', 'name', 'latitude', 'longitude')
            ->whereNotNull('latitude')->whereNotNull('longitude')->get();

        $distances = [];
        foreach ($cafes as $cafe) {
            $distance = $this->hitungJarakHaversine($userLatitude, $userLongitude, (float) $cafe->latitude, (float) $cafe->longitude);
            // IMPORTANT: Ensure the key for distance matches how it's retrieved later
            $distances[] = ['cafe_name' => $cafe->name, 'distance' => round($distance, 2)];
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
            ->select('id', 'name', 'menu', 'price', 'wifi_speed', 'mosque', 'latitude', 'longitude') // Added lat/lon for context if needed
            ->get();

        if ($alternativesData->isEmpty()) {
            return ['error' => "No alternative (cafe) data found."];
        }

        // 3. Hitung jarak
        $cafeDistances = $this->getDistance($userId);

        // dd($cafeDistances);
        if (empty($cafeDistances) && $userId) { // Only error out if userId was provided and distances couldn't be fetched
            // This implies user location might be missing. For some criteria, this might be okay,
            // but for distance, it's an issue. We'll use a large default.
            // Or, you could return an error:
            // return ['error' => "Could not calculate distances, user location might be missing for user_id: {$userId}."];
        }

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
                (float) $alt->distance // ensure this is correctly populated
            ];
        })->all();


        if (empty($matrix)) {
            return ['error' => "Decision matrix could not be formed."];
        }
        // 5. Gunakan bobot dari preferensi
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
            // If all raw weights are 0, distribute equally
            $weights = array_fill(0, count($rawWeights), 1 / count($rawWeights));
        } else {
            return ['error' => "No criteria weights available from preference."];
        }

        // Define Cost/Benefit for each criterion
        // menu (benefit=1), price (cost=0), wifi_speed (benefit=1), mosque (benefit=1), distance (cost=0)
        $costBenefit = [1, 0, 1, 1, 0];

        // 6. Lakukan kalkulasi MABAC
        $normalized = $this->normalize($matrix, $costBenefit);
        $weighted = $this->applyWeight($normalized, $weights);
        $border = $this->borderApproximation($weighted);
        $distanceToBorder = $this->distanceToBorder($weighted, $border);

        // Prepare data for ranking (cafe names specifically)
        // The rankingScore method needs a list of items that have 'cafe_name'
        $cafeIdentifiersForRanking = $alternativesData->map(function ($alt) {
            return ['cafe_name' => $alt->name]; // Only name is needed for ranking output structure
        })->all();
        $ranking = $this->rankingScore($distanceToBorder, $cafeIdentifiersForRanking);

        return [
            'requested_user_id' => $userId, // Match API output
            'matrix' => $matrix,
            'cost_benefit_applied' => $costBenefit, // Match API output
            'raw_weights' => $rawWeights,
            'normalized_weights_used' => $weights, // Match API output ('normalized_weights_used')
            'normalized_matrix' => $normalized,
            'weighted_matrix' => $weighted,
            'border_approximation' => $border,
            'distance_to_border' => $distanceToBorder,
            'ranking_score' => $ranking, // Match API output ('ranking_score')
            // 'preference_created_at' => $userWeightData->created_at->toDateTimeString(), // Optional, not in API output
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
            if (empty($col)) { // Handle case where a column might be empty
                foreach ($matrix as $i => $_) {
                    $norm[$i][$j] = 0;
                }
                continue;
            }
            $min = min($col);
            $max = max($col);

            foreach ($matrix as $i => $row) {
                if ($max == $min) {
                    $norm[$i][$j] = 0; // Avoid division by zero, or assign 1 if all values are same and beneficial
                } else {
                    if ($costBenefit[$j] == 1) { // Benefit attribute
                        $norm[$i][$j] = ($row[$j] - $min) / ($max - $min);
                    } else { // Cost attribute
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
                // Ensure weight exists for this criterion, otherwise, it implies a mismatch
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
            $product = 1.0; // Use float
            $actualValuesInProduct = 0;
            foreach ($weighted as $row) {
                // Ensure column $j exists in row
                if (isset($row[$j])) {
                    $value = (float) $row[$j];
                    // MABAC's border approximation uses geometric mean. Values must be > 0.
                    // A common practice is to add a small epsilon if a value is 0 or very close,
                    // or handle it as per specific MABAC variant rules.
                    // The reference API output suggests 0.0001 for 0 values.
                    $product *= ($value <= 0 ? 0.0001 : $value);
                    $actualValuesInProduct++;
                }
            }
            if ($actualValuesInProduct > 0) {
                $border[$j] = pow($product, 1.0 / $actualValuesInProduct);
            } else {
                $border[$j] = 0.0001; // Default if no values found for a criterion
            }
        }
        return $border;
    }

    protected function distanceToBorder($weighted, $border)
    {
        $distance = [];
        foreach ($weighted as $i => $row) {
            foreach ($row as $j => $value) {
                // Ensure border value exists for this criterion
                $distance[$i][$j] = $value - ($border[$j] ?? 0);
            }
        }
        return $distance;
    }

    // Renamed $distances parameter to $cafeIdentifiers for clarity
    protected function rankingScore(array $distanceToBorder, array $cafeIdentifiers)
    {
        $results = [];
        // $cafeIdentifiers is expected to be like: [['cafe_name' => 'Cafe A'], ['cafe_name' => 'Cafe B'], ...]
        // $distanceToBorder is indexed 0, 1, 2... corresponding to the alternatives' order
        foreach ($cafeIdentifiers as $key => $item) {
            $results[] = [
                'cafe_name' => $item['cafe_name'] ?? 'Unknown Cafe', // Ensure 'cafe_name' key exists
                'score' => isset($distanceToBorder[$key]) && is_array($distanceToBorder[$key])
                    ? array_sum($distanceToBorder[$key])
                    : 0 // Default score if data is missing
            ];
        }

        $sortedResults = $results; // Use $results directly for sorting
        usort($sortedResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'score' => $results, // Original order of scores
            'sorted_names' => array_column($sortedResults, 'cafe_name'),
        ];
    }
}
