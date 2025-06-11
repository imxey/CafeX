@props(['date', 'preferences'])

<div class="flex flex-col gap-4 w-full md:w-3/4 lg:w-1/2 mt-8 px-4">
    <h3 class="text-black font-semibold text-lg">{{ $date }}</h3>

    @if($preferences && $preferences->count() > 0)
    @foreach ($preferences as $preference)
    <div class="border-2 border-gray-300 rounded-md shadow-sm bg-white"
        x-data="historyItemData({{ $preference->id }}, {{ Auth::id() }})"> {{-- This Alpine component is for the individual history item's accordion and data fetching --}}

        {{-- Button to toggle the list of recommended cafes for this history entry --}}
        <button
            @click="toggleOpen()"
            type="button"
            class="w-full select-none cursor-pointer hover:bg-gray-100 text-black px-6 py-4 flex justify-between items-center rounded-t-md"
            :class="{ 'rounded-b-md': !isOpen, 'bg-gray-100': isOpen }">
            <p class="text-md font-semibold">{{ $preference->created_at->format('H:i:s') }}</p>
            <svg class="w-5 h-5 text-gray-600 transition-transform duration-300" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        {{-- Content of the history item (list of recommended cafes) --}}
        <div x-show="isOpen" x-collapse class="p-4 border-t border-gray-300">
            <div x-show="isLoading" class="text-center py-3">
                <p class="text-gray-500 italic">Loading recommendations...</p>
            </div>
            <div x-show="error" class="text-center py-3 text-red-500" x-text="error"></div>

            <div x-show="!isLoading && !error && recommendations.length > 0" class="space-y-3">
                <template x-for="(rec, index) in recommendations" :key="rec.id || index">
                    {{-- Button to open the GLOBAL MABAC modal with this specific recommendation's details --}}
                    <button
                        @click="$dispatch('open-mabac-modal', {
                                    rank: rec.rank,
                                    title: rec.name,
                                    image: rec.image_url,
                                    address: rec.address,
                                    maps: rec.maps,
                                    openTime: rec.open_time,
                                    closeTime: rec.close_time,
                                    normalizedData: rec.normalized_matrix_row,
                                    weightedData: rec.weighted_matrix_row,
                                    borderApproximation: borderApproximationGlobal, {{-- From historyItemData --}}
                                    distanceData: rec.distance_to_border_row,
                                    rankingScoreValue: rec.ranking_score_value,
                                    recommendationDateTime: recommendationDateTimeForModal {{-- From historyItemData --}}
                                })"
                        class="w-full text-left select-none cursor-pointer hover:bg-gray-200 bg-gray-100 text-gray-800 border border-gray-300 px-4 py-3 flex justify-between items-center rounded-md shadow-sm hover:shadow-md transition-shadow">
                        <span class="font-medium text-sm" x-text="`#${rec.rank} ${rec.name}`"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-right text-gray-500" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                        </svg>
                    </button>
                </template>
            </div>
            <div x-show="!isLoading && !error && recommendations.length === 0 && isOpen" class="text-center py-3 text-gray-500">
                No recommendations were found for this specific history entry.
            </div>
        </div>
    </div>
    @endforeach
    @else
    <p class="text-sm text-gray-500">No entries for this date.</p>
    @endif
</div>

@once
@push('scripts')
<script>
    function historyItemData(pId, uId) {
        return {
            isOpen: false, // For this specific history item's accordion
            isLoading: false,
            recommendations: [],
            borderApproximationGlobal: [], // Fetched data for the modal
            recommendationDateTimeForModal: '', // Fetched data for the modal
            error: null,
            preferenceId: pId,
            userId: uId,
            toggleOpen() {
                this.isOpen = !this.isOpen;
                if (this.isOpen && this.recommendations.length === 0 && !this.isLoading && !this.error) {
                    this.fetchRecommendations();
                }
            },
            fetchRecommendations() {
                if (this.userId === null) {
                    this.error = "User not authenticated.";
                    this.isLoading = false;
                    return;
                }
                this.isLoading = true;
                this.error = null;
                fetch(`{{ route('history.recommendation.details') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            preference_id: this.preferenceId,
                            user_id: this.userId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.error || `Error: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.recommendations) {
                            this.recommendations = data.recommendations;
                            this.borderApproximationGlobal = data.border_approximation || [];
                            this.recommendationDateTimeForModal = data.recommendation_datetime_formatted || new Date().toLocaleString('id-ID', {
                                /* date options */
                            });
                            this.error = null;
                        } else {
                            this.error = data.error || "Invalid data from history details.";
                            this.recommendations = [];
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching historic recommendations for preference ' + this.preferenceId + ':', error);
                        this.error = error.message || 'Failed to load recommendations.';
                        this.recommendations = [];
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            }
        };
    }
</script>
@endpush
@endonce
