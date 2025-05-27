<?php

namespace App\Http\Controllers;

use App\Models\Preferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // To get the logged-in user
use Carbon\Carbon;

class History extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }


        $preferences = Preferences::where('user_id', $userId)
            ->latest('created_at')
            ->get();

        // Debugging step: Check the type of created_at for the first item
        // if ($preferences->isNotEmpty()) {
        //     dump($preferences->first()->created_at);
        //     dump(get_class($preferences->first()->created_at)); // Should be Carbon\Carbon
        // }

        $groupedHistory = $preferences->groupBy(function ($item) {
            // Ensure created_at is a Carbon instance before calling format
            $createdAtDate = $item->created_at;
            if (!$createdAtDate instanceof Carbon) {
                $createdAtDate = Carbon::parse($createdAtDate); // Parse if it's a string
            }
            return $createdAtDate->format('Y-m-d');
        })->map(function ($dailyPreferences, $dateKey) {
            return [
                'formatted_date' => Carbon::parse($dateKey)->isoFormat('dddd, D-M-YYYY'),
                'preferences' => $dailyPreferences
            ];
        });

        return view('history', ['groupedHistory' => $groupedHistory]);
    }
}
