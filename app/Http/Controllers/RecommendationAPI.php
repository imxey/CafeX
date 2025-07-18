<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationAPI extends Controller
{
    private function hitungJarakHaversine(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB,
        float $earthRadius = 6371.0
    ): float {

        $latA_rad = deg2rad($latitudeA);
        $lonA_rad = deg2rad($longitudeA);
        $latB_rad = deg2rad($latitudeB);
        $lonB_rad = deg2rad($longitudeB);


        $deltaLat = $latB_rad - $latA_rad;
        $deltaLon = $lonB_rad - $lonA_rad;


        $a = pow(sin($deltaLat / 2), 2) +
            cos($latA_rad) * cos($latB_rad) *
            pow(sin($deltaLon / 2), 2);


        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));


        $jarak = $earthRadius * $c;

        return $jarak;
    }
    public function getDistance(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Heey, user_id-nya mana di body request? Aku butuh itu!'], 400);
        }


        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user || $user->longitude === null || $user->latitude === null) {
            return response()->json(['error' => 'Lokasi user ini (' . $userId . ') gak ketemu atau gak lengkap, beb!'], 404);
        }
        $userLongitude = (float) $user->longitude;
        $userLatitude = (float) $user->latitude;


        $cafes = DB::table('cafes')
            ->select('id', 'name', 'latitude', 'longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($cafes->isEmpty()) {
            return response()->json(['error' => 'Duh, data cafe-nya gak ada atau gak ada yang punya koordinat lengkap!'], 404);
        }

        $distances = [];
        foreach ($cafes as $cafe) {
            $cafeLatitude = (float) $cafe->latitude;
            $cafeLongitude = (float) $cafe->longitude;


            $distance = $this->hitungJarakHaversine(
                $userLatitude,
                $userLongitude,
                $cafeLatitude,
                $cafeLongitude
            );

            $distances[] = [
                'cafe_name' => $cafe->name,
                round($distance, 2)
            ];
        }

        if (empty($distances)) {
            return response()->json(['error' => 'Gak ada jarak yang bisa dihitung, mungkin semua cafe gak punya koordinat? Aneh!'], 500);
        }


        return $distances;
    }
    public function calculate(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Heey, user_id-nya mana di body request?'], 400);
        }
        $alternativesData = DB::table('cafes')
            ->select('menu', 'price', 'wifi_speed', 'mosque')
            ->get();

        if ($alternativesData->isEmpty()) {
            return response()->json(['error' => 'Duh, data alternatifnya gak ada di DB!'], 404);
        }
        $distance = $this->getDistance($request);
        foreach ($alternativesData as $key => $alt) {
            if (isset($distance[$key]) && isset($distance[$key][0])) {
                $alt->distance = $distance[$key][0];
            } else {
                $alt->distance = 0;
            }
        }
        $matrix = [];
        foreach ($alternativesData as $alt) {
            $matrix[] = [
                $alt->menu,
                $alt->price,
                $alt->wifi_speed,
                $alt->mosque,
                $alt->distance
            ];
        }
        if (empty($matrix) || empty($matrix[0])) {
            return response()->json(['error' => 'Matriksnya kosong'], 500);
        }
        $userWeightData = DB::table('preferences')
            ->select('preference_menu', 'preference_price', 'preference_wifi_speed', 'preference_mosque', 'preference_distance')
            ->where('user_id', $userId)
            ->latest()->first();
        if (!$userWeightData) {
            return response()->json(['error' => 'Bobot buat user ini (' . $userId . ') gak ketemu'], 404);
        }
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
            foreach ($rawWeights as $rw) {
                $weights[] = $rw / $sumRawWeights;
            }
        } else {
            $criteriaCount = count($rawWeights);
            if ($criteriaCount > 0) {
                $weights = array_fill(0, $criteriaCount, 1 / $criteriaCount);
            } else {
                return response()->json(['error' => 'Gak ada bobot kriteria yang bisa dipake nih!'], 400);
            }
        }

        $costBenefit = [1, 0, 1, 1, 0];

        $normalized = $this->normalize($matrix, $costBenefit);
        $weighted = $this->applyWeight($normalized, $weights);

        if (empty($weighted) || empty($weighted[0])) {
            return response()->json(['error' => 'Matriks terbobotnya kosong setelah normalisasi/pembobotan!'], 500);
        }
        $border = $this->borderApproximation($weighted);
        $distanceToBorder = $this->distanceToBorder($weighted, $border);
        $ranking = $this->rankingScore($distanceToBorder, $request);
        $distance = $this->getDistance($request);
        return response()->json([
            'requested_user_id' => $userId,
            'matrix' => $matrix,
            'cost_benefit_applied' => $costBenefit,
            'raw_weights' => $rawWeights,
            'normalized_matrix' => $normalized,
            'normalized_weights_used' => $weights,
            'weighted_matrix' => $weighted,
            'border_approximation' => $border,
            'distance_to_border' => $distanceToBorder,
            'ranking_score' => $ranking,
        ]);
    }
    private function normalize($matrix, $costBenefit)
    {
        $norm = [];

        if (empty($matrix) || empty($matrix[0])) {
            return $norm;
        }
        $colCount = count($matrix[0]);
        for ($j = 0; $j < $colCount; $j++) {
            $col = array_column($matrix, $j);
            if (empty($col)) {
                continue;
            }
            $min = min($col);
            $max = max($col);
            foreach ($matrix as $i => $row) {
                if (!isset($norm[$i])) {
                    $norm[$i] = [];
                }
                if (!isset($costBenefit[$j])) {
                    $norm[$i][$j] = 0;
                    continue;
                }
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

    private function applyWeight($normalized, $weights)
    {
        $weighted = [];
        foreach ($normalized as $i => $row) {
            foreach ($row as $j => $value) {
                if (!isset($weights[$j])) {
                    $weighted[$i][$j] = 0;
                    continue;
                }
                $weighted[$i][$j] = $value * $weights[$j];
            }
        }
        return $weighted;
    }

    private function borderApproximation($weighted)
    {
        $border = [];

        $colCount = count($weighted[0]);
        $rowCount = count($weighted);

        if ($rowCount == 0) {
            return $border;
        }

        for ($j = 0; $j < $colCount; $j++) {
            $product = 1;
            for ($i = 0; $i < $rowCount; $i++) {
                $value = $weighted[$i][$j];
                // Cegah pembagian 0 atau nilai 0
                if ($value <= 0) {
                    $value = 0.0001;
                }
                $product *= $value;
            }
            $border[$j] = pow($product, 1 / $rowCount);
        }

        return $border;
    }

    private function distanceToBorder($weighted, $border)
    {
        $distance = [];
        foreach ($weighted as $i => $row) {
            foreach ($row as $j => $value) {
                if (!isset($border[$j])) {
                    $distance[$i][$j] = $value;
                    continue;
                }
                $distance[$i][$j] = $value - $border[$j];
            }
        }
        return $distance;
    }

    private function rankingScore(array $distanceToBorder, Request $request)
    {
        $distances = $this->getDistance($request);

        $results = [];
        foreach ($distances as $key => $item) {
            $cafeName = $item['cafe_name'] ?? '';

            $scoreValue = isset($distanceToBorder[$key])
                ? array_sum($distanceToBorder[$key])
                : 0;

            $results[] = [
                'cafe_name' => $cafeName,
                'score'     => $scoreValue,
            ];
        }

        $sortedResults = $results;
        usort($sortedResults, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $sortedNames = array_map(function ($item) {
            return $item['cafe_name'];
        }, $sortedResults);

        return [
            'score' => $results,
            'sorted_names' => $sortedNames,
        ];
    }

    private function fetchRecommendationDataFromApi(int $userId): ?array
    {
        $apiUrl = 'http://127.0.0.1:8000/api/recommendation';
        Log::info("Attempting to fetch recommendation API for user_id: {$userId} from URL: {$apiUrl}");

        $response = Http::post($apiUrl, ['user_id' => $userId]);

        if (!$response->successful()) {
            Log::error("API request failed for user_id {$userId}. Status: {$response->status()}. Body: " . $response->body());

            $response->throw();
        }

        Log::info("API request successful for user_id {$userId}.");
        return $response->json();
    }


    public function getRecommendations(Request $request)
    {
        $userId = 1;

        if (Auth::check()) {
            $loggedInUser = Auth::user();
            $userId = $loggedInUser->id;
            Log::info('Recommendation page: Logged in user ID: ' . $userId);
        } else {
            Log::info('Recommendation page: Guest user ID (default): ' . $userId);
        }

        try {
            $apiData = $this->fetchRecommendationDataFromApi($userId);

            if ($apiData === null) {
                Log::error('Received null data from API fetch for user_id ' . $userId);
                return view('recommendation', [
                    'recommendations' => [],
                    'border_approximation' => [],
                    'error' => 'Failed to retrieve data from the recommendation service.'
                ]);
            }

            if (!isset($apiData['ranking_score']['score']) || !isset($apiData['ranking_score']['sorted_names'])) {
                Log::error('API response missing expected ranking_score data for user_id ' . $userId . ': ' . json_encode($apiData));
                return view('recommendation', [
                    'recommendations' => [],
                    'border_approximation' => [],
                    'error' => 'Recommendation data format is incorrect.'
                ]);
            }

            // 1. Ambil semua cafe dari DB
            $apiCafeNames = array_column($apiData['ranking_score']['score'], 'cafe_name');
            $cafesFromDb = Cafe::whereIn('name', $apiCafeNames)->get()->keyBy('name');

            // 2. Buat mapping nama cafe ke index original
            $cafeNameToOriginalIndex = [];
            foreach ($apiData['ranking_score']['score'] as $index => $scoreItem) {
                $cafeNameToOriginalIndex[$scoreItem['cafe_name']] = $index;
            }

            // 3. Siapkan data untuk view
            $recommendations = [];
            $rank = 1;

            foreach ($apiData['ranking_score']['sorted_names'] as $sortedCafeName) {
                if (isset($cafesFromDb[$sortedCafeName]) && isset($cafeNameToOriginalIndex[$sortedCafeName])) {
                    $cafeDb = $cafesFromDb[$sortedCafeName];
                    $originalIndex = $cafeNameToOriginalIndex[$sortedCafeName];

                    $currentCafeScore = 0;
                    foreach ($apiData['ranking_score']['score'] as $scoreDetail) {
                        if ($scoreDetail['cafe_name'] === $sortedCafeName) {
                            $currentCafeScore = $scoreDetail['score'];
                            break;
                        }
                    }

                    $recommendations[] = [
                        'rank' => $rank++,
                        'id' => $cafeDb->id,
                        'name' => $cafeDb->name,
                        'address' => $cafeDb->address ?: 'Alamat tidak tersedia',
                        'image_url' => $cafeDb->image_url ?: '/images/default-cafe.png',
                        'open_time' => $cafeDb->open_time ? \Carbon\Carbon::parse($cafeDb->open_time)->format('g a') : 'N/A',
                        'close_time' => $cafeDb->close_time ? \Carbon\Carbon::parse($cafeDb->close_time)->format('g a') : 'N/A',
                        'normalized_matrix_row' => $apiData['normalized_matrix'][$originalIndex] ?? [],
                        'weighted_matrix_row' => $apiData['weighted_matrix'][$originalIndex] ?? [],
                        'distance_to_border_row' => $apiData['distance_to_border'][$originalIndex] ?? [],
                        'ranking_score_value' => $currentCafeScore,
                    ];
                } else {
                    Log::warning("Cafe '{$sortedCafeName}' from API (user_id {$userId}) not found in DB or missing original index.");
                }
            }

            $borderApproximation = $apiData['border_approximation'] ?? [];

            return view('recommendation', [
                'recommendations' => $recommendations,
                'border_approximation' => $borderApproximation,
            ]);
        } catch (ConnectionException $e) {
            Log::error('API Connection Exception for user_id ' . $userId . ' in getRecommendations: ' . $e->getMessage());
            return view('recommendation', [
                'recommendations' => [],
                'border_approximation' => [],
                'error' => 'Could not connect to the recommendation service. Please try again later.'
            ]);
        } catch (RequestException $e) {
            Log::error('API Request Exception for user_id ' . $userId . ' in getRecommendations: ' . $e->getMessage() . ' Response: ' . $e->response->body());
            return view('recommendation', [
                'recommendations' => [],
                'border_approximation' => [],
                'error' => 'Failed to fetch recommendations from API. Service returned an error. (Status: ' . $e->response->status() . ')'
            ]);
        } catch (\Exception $e) {
            Log::error('General Error for user_id ' . $userId . ' in getRecommendations: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return view('recommendation', [
                'recommendations' => [],
                'border_approximation' => [],
                'error' => 'An unexpected error occurred while preparing recommendations.'
            ]);
        }
    }
}
