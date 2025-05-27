<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

// Import Collection

class HistorySection extends Component
{
    public string $date;
    public Collection $preferences;

    /**
     * Create a new component instance.
     *
     * @param string                         $date
     * @param \Illuminate\Support\Collection $preferences
     * @return void
     */
    public function __construct(string $date, Collection $preferences)
    {
        $this->date = $date;
        $this->preferences = $preferences;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.history-section');
    }
}
