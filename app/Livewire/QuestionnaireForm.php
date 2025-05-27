<?php

namespace App\Livewire;

use App\Models\Preferences;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class QuestionnaireForm extends Component
{
    public $currentQuestion = 1;
    public $answers = [];

    public $options = [
        1 => 'Sangat Tidak Penting',
        2 => 'Tidak Penting',
        3 => 'Cukup',
        4 => 'Penting',
        5 => 'Sangat Penting',
    ];

    public $questions = [
        1 => "Dari skala 1 - 5 seberapa penting Harga Menu bagi anda untuk memilih cafe yang akan dikunjungi?",
        2 => "Dari skala 1 - 5 seberapa penting Jarak bagi anda untuk memilih cafe yang akan dikunjungi?",
        3 => "Dari skala 1 - 5 seberapa penting Variasi Menu bagi anda untuk memilih cafe yang akan dikunjungi?",
        4 => "Dari skala 1 - 5 seberapa penting Ketersediaan Mushola bagi anda untuk memilih cafe yang akan dikunjungi?",
        5 => "Dari skala 1 - 5 seberapa penting Kecepatan Wifi bagi anda untuk memilih cafe yang akan dikunjungi?",
    ];

    public function selectAnswer($option)
    {
        $this->answers[$this->currentQuestion] = (int) $option;
    }


    public function next($selectedOption = null)
    {
        if ($selectedOption) {
            $this->answers[$this->currentQuestion] = (int) $selectedOption;
        }

        if ($this->currentQuestion < count($this->questions)) {
            $this->currentQuestion++;
        }
    }

    public function previous()
    {
        if ($this->currentQuestion > 1) {
            $this->currentQuestion--;
        }
    }

    public function save()
    {
        try {
            $preference = Preferences::create([
                'user_id' => Auth::user()->id,
                'preference_price' => $this->answers[1] ?? 3,
                'preference_distance' => $this->answers[2] ?? 3,
                'preference_menu' => $this->answers[3] ?? 3,
                'preference_mosque' => $this->answers[4] ?? 3,
                'preference_wifi_speed' => $this->answers[5] ?? 3
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Preferensi Anda berhasil disimpan!'
            );

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Gagal menyimpan preferensi: ' . $e->getMessage()
            );
            // Untuk debugging, Anda bisa tambahkan:
            logger()->error('Error saving preferences: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.questionnaire-form')->extends('layouts.without-navbar');
    }
}
