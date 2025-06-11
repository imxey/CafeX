<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class Preferences extends Controller
{
    public $questions = [
        1 => "Dari skala 1 - 5 seberapa penting Harga Menu bagi anda untuk memilih cafe yang akan dikunjungi?",
        2 => "Dari skala 1 - 5 seberapa penting Jarak bagi anda untuk memilih cafe yang akan dikunjungi?",
        3 => "Dari skala 1 - 5 seberapa penting Variasi Menu bagi anda untuk memilih cafe yang akan dikunjungi?",
        4 => "Dari skala 1 - 5 seberapa penting Ketersediaan Mushola bagi anda untuk memilih cafe yang akan dikunjungi?",
        5 => "Dari skala 1 - 5 seberapa penting Kecepatan Wifi bagi anda untuk memilih cafe yang akan dikunjungi?",
    ];

    public $options = [
        1 => 'Sangat Tidak Penting',
        2 => 'Tidak Penting',
        3 => 'Cukup',
        4 => 'Penting',
        5 => 'Sangat Penting',
    ];

    public function index()
    {
        return view('preferences-form')->with([
            'questions' => $this->questions,
            'options' => $this->options
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|integer|between:1,5'
        ]);

        $user = Auth::user();
        $user->preferences()->create([
            'user_id' => $user->id,
            'preference_price' => $validated['answers'][0] ?? 3,
            'preference_menu' => $validated['answers'][1] ?? 3,
            'preference_distance' => $validated['answers'][2] ?? 3,
            'preference_mosque' => $validated['answers'][3] ?? 3,
            'preference_wifi_speed' => $validated['answers'][4] ?? 3
        ]);
        return redirect('/');
    }
}
