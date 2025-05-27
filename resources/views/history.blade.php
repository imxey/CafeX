<x-app-layout>
    <div class="flex justify-center items-center flex-col pt-12">
        <h1 class="font-bold text-2xl mb-4">History</h1>

        @if($groupedHistory && $groupedHistory->count() > 0)
        @foreach ($groupedHistory as $historyGroup)
        {{--
                    $historyGroup is an array like:
                    ['formatted_date' => 'Tuesday, 27-5-2025', 'preferences' => CollectionOfPreferences]
                --}}
        <x-history-section
            :date="$historyGroup['formatted_date']"
            :preferences="$historyGroup['preferences']" />
        @endforeach
        @else
        <p class="mt-8 text-gray-600">No history records found.</p>
        @endif
    </div>
</x-app-layout>
