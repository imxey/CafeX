<?php

namespace App\Http\Controllers;

use App\Models\Cafe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\MABAC;
use Illuminate\Support\Facades\DB;

class Recommendation extends Controller
{
    protected MABAC $mabacService;

    public function __construct(MABAC $mabacService)
    {
        $this->mabacService = $mabacService;
    }

    public function getRecommendations(Request $request)
    {
        $userId = 1; // Default for guest, will be overridden if logged in

        if (Auth::check()) {
            $loggedInUser = Auth::user();
            $userId = $loggedInUser->id;
            Log::info('Recommendation page: Logged in user ID: ' . $userId);
        } else {
            Log::info('Recommendation page: Guest user ID (default): ' . $userId);
        }

        try {
            // Assuming you might want to pass a specific preference ID in the future
            // For now, it will get the latest preference for the user
            $mabacResult = $this->mabacService->calculate($userId, null);


            // Check for error from MABAC service first
            if (isset($mabacResult['error'])) {
                Log::error('MABAC service returned an error for user_id ' . $userId . ': ' . $mabacResult['error']);
                return view('recommendation', [
                    'recommendations' => [],
                    'border_approximation' => [],
                    'error' => $mabacResult['error'],
                    'recommendation_datetime_formatted' => Carbon::now()->format('l, d-m-Y H:i:s'),
                ]);
            }

            // Adjusted keys based on MABAC service's new output
            if (empty($mabacResult) || !isset($mabacResult['ranking_score']) || !isset($mabacResult['ranking_score']['score'])) {
                Log::error('Received empty or malformed data from MABAC service for user_id ' . $userId . ': ' . json_encode($mabacResult));
                return view('recommendation', [
                    'recommendations' => [],
                    'border_approximation' => [],
                    'error' => 'Failed to retrieve data from the MABAC service or data is incomplete.',
                    'recommendation_datetime_formatted' => Carbon::now()->format('l, d-m-Y H:i:s'),
                ]);
            }

            $rankingData = $mabacResult['ranking_score']; // Use 'ranking_score'

            // Fetch preference creation time for display (if you decide to keep it in MABAC output)
            // If not, you might need to fetch it separately or use current time
            $recommendationDateTimeString = null;
            if (isset($mabacResult['preference_created_at'])) { // Check if MABAC service provides it
                $recommendationDateTimeString = $mabacResult['preference_created_at'];
            } else {
                $userPreference = DB::table('preferences')->where('user_id', $userId)->latest('created_at')->first();
                $recommendationDateTimeString = $userPreference->created_at ?? now()->toDateTimeString();
            }


            try {
                $formattedDateTime = Carbon::parse($recommendationDateTimeString)->format('l, d-m-Y H:i:s');
            } catch (\Exception $e) {
                Log::warning("Failed to parse date for recommendation: {$recommendationDateTimeString}. Error: {$e->getMessage()}");
                $formattedDateTime = Carbon::now()->format('l, d-m-Y H:i:s');
            }

            $apiCafeNames = array_column($rankingData['score'], 'cafe_name');
            $cafesFromDb = Cafe::whereIn('name', $apiCafeNames)->get()->keyBy('name');

            $cafeNameToIndexMap = [];
            foreach ($rankingData['score'] as $index => $scoreItem) {
                $cafeNameToIndexMap[$scoreItem['cafe_name']] = $index;
            }

            $recommendations = [];
            $rank = 1;

            // Use 'sorted_names' from 'ranking_score'
            foreach ($rankingData['sorted_names'] as $sortedCafeName) {
                if (isset($cafesFromDb[$sortedCafeName]) && isset($cafeNameToIndexMap[$sortedCafeName])) {
                    $cafeDb = $cafesFromDb[$sortedCafeName];
                    $originalIndex = $cafeNameToIndexMap[$sortedCafeName];

                    $currentCafeScore = 0;
                    foreach ($rankingData['score'] as $scoreDetail) {
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
                        'maps' => $cafeDb->maps,
                        'image_url' => $cafeDb->image_url ?: '/images/default-cafe.png',
                        'open_time' => $cafeDb->open_time ? Carbon::parse($cafeDb->open_time)->format('g a') : 'N/A',
                        'close_time' => $cafeDb->close_time ? Carbon::parse($cafeDb->close_time)->format('g a') : 'N/A',
                        'normalized_matrix_row' => $mabacResult['normalized_matrix'][$originalIndex] ?? [],
                        'weighted_matrix_row' => $mabacResult['weighted_matrix'][$originalIndex] ?? [],
                        'distance_to_border_row' => $mabacResult['distance_to_border'][$originalIndex] ?? [],
                        'ranking_score_value' => $currentCafeScore,
                    ];
                } else {
                    Log::warning("Cafe '{$sortedCafeName}' from MABAC service (user_id {$userId}) not found in DB or missing index map.");
                }
            }

            $borderApproximation = $mabacResult['border_approximation'] ?? [];

            return view('recommendation', [
                'recommendations' => $recommendations,
                'border_approximation' => $borderApproximation,
                'recommendation_datetime_formatted' => $formattedDateTime,
                // Pass other MABAC details if your view needs them
                'mabac_matrix' => $mabacResult['matrix'] ?? [],
                'mabac_weights' => $mabacResult['normalized_weights_used'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('General Error for user_id ' . $userId . ' in getRecommendations (using MABAC service): ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return view('recommendation', [
                'recommendations' => [],
                'border_approximation' => [],
                'recommendation_datetime_formatted' => Carbon::now()->format('l, d-m-Y H:i:s'),
                'error' => 'An unexpected error occurred while preparing recommendations using MABAC service.'
            ]);
        }
    }
}
