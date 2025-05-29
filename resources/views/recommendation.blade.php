<x-app-layout>
    <div class="py-10">
        <div class="w-full sm:px-6 lg:px-8">
            @if(isset($error) && $error)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ $error }}</span>
            </div>
            @endif

            @if(isset($recommendations) && count($recommendations) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10"> {{-- Tambah gap-y --}}
                @foreach ($recommendations as $rec)
                <x-recommendation-card
                    :rank="$rec['rank']"
                    :title="$rec['name']"
                    :image="asset($rec['image_url'])" {{-- Pastikan image_url adalah path yang benar dari public --}}
                    :address="$rec['address']"
                    :openTime="$rec['open_time']"
                    :closeTime="$rec['close_time']"
                    {{-- Teruskan data MABAC ke komponen card --}}
                    :normalizedData="$rec['normalized_matrix_row']"
                    :weightedData="$rec['weighted_matrix_row']"
                    :borderApproximation="$border_approximation" {{-- Ini global --}}
                    :distanceData="$rec['distance_to_border_row']"
                    :rankingScoreValue="$rec['ranking_score_value']" />
                @endforeach
            </div>
            @elseif(!isset($error)) {{-- Hanya tampilkan "No recommendations" jika tidak ada error --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("No recommendations found at the moment.") }}
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
