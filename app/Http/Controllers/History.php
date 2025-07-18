<?php

namespace App\Http\Controllers;

use App\Models\Preferences;
use App\Models\Cafe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\MABACHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class History extends Controller
{
    protected MABACHistory $mabacHistoryService;

    public function __construct(MABACHistory $mabacHistoryService)
    {
        $this->mabacHistoryService = $mabacHistoryService;
    }

    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login')->with('error', 'You must be logged in to view history.');
        }
        $preferences = Preferences::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        $groupedHistory = $preferences->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        })->map(function ($dailyPreferences, $dateKey) {
            return [
                'formatted_date' => Carbon::parse($dateKey)->isoFormat('dddd, D MMMM YYYY'),
                'preferences' => $dailyPreferences,
            ];
        });
        return view('history', ['groupedHistory' => $groupedHistory]);
    }


    public function getHistoricRecommendationDetails(Request $request)
    {
        $request->validate([
            'preference_id' => 'required|integer|exists:preferences,id',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
        $preferenceId = (int) $request->input('preference_id');

        // Verify the preference belongs to the authenticated user
        $preference = Preferences::where('id', $preferenceId)->where('user_id', $userId)->first();
        if (!$preference) {
            return response()->json(['error' => 'Preference not found or access denied.'], 404);
        }

        try {
            $mabacResult = $this->mabacHistoryService->calculateHistoricMabac($userId, $preferenceId);

            // Check against the new MABAC result structure
            if (
                isset($mabacResult['error']) ||
                empty($mabacResult) ||
                !isset($mabacResult['ranking_score']) ||
                !isset($mabacResult['ranking_score']['sorted_items']) ||
                !isset($mabacResult['preference_created_at'])
            ) {
                Log::error("MABACHistory returned error or incomplete data for user {$userId}, pref {$preferenceId}: " . json_encode($mabacResult));
                return response()->json(['error' => $mabacResult['error'] ?? 'Failed to calculate MABAC for the selected history.'], 500);
            }

            $formattedDateTime = Carbon::parse($mabacResult['preference_created_at'])->format('l, d-m-Y H:i:s');

            // Use 'sorted_items' from 'ranking_score'
            $rankedCafeItems = $mabacResult['ranking_score']['sorted_items'];
            $rankedCafeIds = array_column($rankedCafeItems, 'cafe_id');

            $cafesDetails = collect();
            if (!empty($rankedCafeIds)) {
                $cafesDetails = Cafe::whereIn('id', $rankedCafeIds)
                    ->get()
                    ->keyBy('id');
            }

            $recommendationsForJs = [];
            $currentRank = 1;

            foreach ($rankedCafeItems as $rankedItem) {
                $cafeDb = $cafesDetails->get($rankedItem['cafe_id']);
                if ($cafeDb) {
                    $originalIndex = -1;
                    foreach ($mabacResult['ranking_score']['score'] as $idx => $rawScoreItem) {
                        if (($rawScoreItem['cafe_id'] ?? null) == $rankedItem['cafe_id']) {
                            $originalIndex = $idx;
                            break;
                        }
                    }

                    if ($originalIndex !== -1 && isset($mabacResult['normalized_matrix'][$originalIndex])) {
                        $recommendationsForJs[] = [
                            'rank' => $currentRank++,
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
                            'ranking_score_value' => $rankedItem['score'],
                        ];
                    } else {
                        Log::warning("Could not find original index or matrix data for cafe_id: {$rankedItem['cafe_id']} in history (pref_id: {$preferenceId}). OriginalIndex: {$originalIndex}. Matrix available: " . (isset($mabacResult['normalized_matrix']) ? 'yes' : 'no'));
                    }
                } else {
                    Log::warning("Cafe details not found in DB for cafe_id: {$rankedItem['cafe_id']} from MABAC historic result (pref_id: {$preferenceId}).");
                }
            }

            return response()->json([
                'recommendations' => $recommendationsForJs,
                'border_approximation' => $mabacResult['border_approximation'] ?? [],
                'recommendation_datetime_formatted' => $formattedDateTime,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getHistoricRecommendationDetails for user {$userId}, pref {$preferenceId}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            Log::error("Stack Trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'An unexpected error occurred while fetching historic details. Please check logs.'], 500);
        }
    }
}
