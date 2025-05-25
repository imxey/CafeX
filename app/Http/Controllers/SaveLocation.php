<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SaveLocation extends Controller
{
    public function saveLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = Auth::user();

            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            return response()->json([
                'message' => 'Location saved successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
