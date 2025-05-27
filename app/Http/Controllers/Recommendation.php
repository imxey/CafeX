<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class Recommendation extends Controller
{
    public function calculate(Request $request) 
    {
        $userId = $request->input('user_id'); 
        if (!$userId) {
            return response()->json(['error' => 'Heey, user_id-nya mana di body request? Aku butuh itu!'], 400); 
        }  
        $alternativesData = DB::table('cafes') 
                                ->select('menu', 'price', 'wifi_speed', 'mosque') 
                                ->get();

        if ($alternativesData->isEmpty()) {
            return response()->json(['error' => 'Duh, data alternatifnya gak ada di DB!'], 404);
        }
        $matrix = [];
        foreach ($alternativesData as $alt) {
            $matrix[] = [
                $alt->menu,
                $alt->price,
                $alt->wifi_speed,
                $alt->mosque
            ];
        }
        if (empty($matrix) || empty($matrix[0])) {
            return response()->json(['error' => 'Matriksnya kosong, ayank!'], 500);
        }
        $userWeightData = DB::table('preferences')
                            ->select('preference_menu', 'preference_price', 'preference_wifi_speed', 'preference_mosque') 
                            ->where('user_id', $userId) 
                            ->first();
        if (!$userWeightData) {
            return response()->json(['error' => 'Bobot buat user ini (' . $userId . ') gak ketemu, beb!'], 404);
        }
        $rawWeights = [
            $userWeightData->preference_menu,
            $userWeightData->preference_price,
            $userWeightData->preference_wifi_speed,
            $userWeightData->preference_mosque
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

        $costBenefit = [1, 0, 1, 1];

        $normalized = $this->normalize($matrix, $costBenefit);
        $weighted = $this->applyWeight($normalized, $weights);
        
        if (empty($weighted) || empty($weighted[0])) {
            return response()->json(['error' => 'Matriks terbobotnya kosong setelah normalisasi/pembobotan!'], 500);
        }
        $border = $this->borderApproximation($weighted);
        $distance = $this->distanceToBorder($weighted, $border);
        $ranking = $this->rankingScore($distance);

        return response()->json([
            'requested_user_id' => $userId, 
            'normalized_weights_used' => $weights,
            'cost_benefit_applied' => $costBenefit,
            'normalized_matrix' => $normalized,
            'weighted_matrix' => $weighted,
            'border_approximation' => $border,
            'distance_to_border' => $distance,
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
            if (empty($col)) continue; 
            $min = min($col);
            $max = max($col);
            foreach ($matrix as $i => $row) {
                if (!isset($norm[$i])) $norm[$i] = [];
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

        if ($rowCount == 0) return $border; 

        for ($j = 0; $j < $colCount; $j++) {
            $sum = 0;
            for ($i = 0; $i < $rowCount; $i++) {
                $sum += $weighted[$i][$j];
            }
            $border[$j] = $sum / $rowCount;
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

    private function rankingScore($distance)
    {
        $scores = [];
        foreach ($distance as $i => $row) {
            $scores[$i] = array_sum($row);
        }
        return $scores;
    }
}