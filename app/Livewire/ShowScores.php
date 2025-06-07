<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Preferences;

class ShowScores extends Component
{
    public $preferences;
    private $userId;

    public function render()
    {
        $this->userId = Auth::user()->id;
        $prefs = Preferences::where('user_id', $this->userId)->get()->last();
        $this->preferences = [
            'Prices' => $prefs->preference_price,
            'Menu Variations' => $prefs->preference_menu,
            'Wifi Speeds' => $prefs->preference_wifi_speed,
            'Prayer Room' => $prefs->preference_mosque,
            'Distance' => $prefs->preference_distance,
        ];
        return view('livewire.show-scores');
    }
}
