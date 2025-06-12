{{-- resources/views/history.blade.php --}}
<x-app-layout>
    <div class="flex justify-center items-center flex-col pt-12">
        <h1 class="font-bold text-2xl mb-4">History</h1>

        @if($groupedHistory && $groupedHistory->count() > 0)
        @foreach ($groupedHistory as $historyGroup)
        <x-history-section
            :date="$historyGroup['formatted_date']"
            :preferences="$historyGroup['preferences']" />
        @endforeach
        @else
        <p class="mt-8 text-gray-600">No history records found.</p>
        @endif
    </div>

    {{-- MODAL MABAC GLOBAL (directly in this file) --}}
    <div x-data="mabacModalController" {{-- This will be our Alpine component name --}}
        @open-mabac-modal.window="openModal($event.detail)" {{-- Listen for the event --}}
        class="fixed inset-0 flex items-center justify-center z-[100] bg-black bg-opacity-75 p-4"
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        style="display: none;">

        <div class="bg-white rounded-lg w-full max-w-2xl p-6 border-black border-2 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] max-h-[90vh] overflow-y-auto"
            @click.away="isOpen = false">
            <div class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4">
                <h2 class="text-xl font-semibold" x-text="modalTitle"></h2>
                <button @click="isOpen = false"
                    class="bg-red-600 w-8 h-8 rounded-full flex items-center justify-center text-white hover:bg-red-700 text-xl font-medium transition-colors">×</button>
            </div>

            <div class="space-y-2">
                <template x-for="(section, index) in sections" :key="index">
                    <div class="border border-gray-300 rounded overflow-hidden">
                        <button @click="openItem = (openItem === index ? null : index)"
                            class="w-full flex items-center justify-between text-left px-4 py-3 font-medium bg-gray-100 hover:bg-gray-200 transition focus:outline-none">
                            <span x-text="section.title" class="text-gray-800"></span>
                            <svg class="w-5 h-5 text-gray-600 transition-transform duration-300" :class="{'rotate-180': openItem === index}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="openItem === index" x-collapse {{-- Add x-collapse if you use the plugin --}}
                            class="px-4 py-3 text-sm text-gray-700 bg-white">
                            <template x-if="section.type === 'array' && Array.isArray(section.content) && section.content.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <template x-for="(headerName, colIndex) in criteriaHeaders" :key="colIndex">
                                                    <template x-if="(Array.isArray(section.content[0]) && colIndex < section.content[0].length) || (!Array.isArray(section.content[0]) && section.content.length > 0 && colIndex < section.content.length)">
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300"
                                                            x-text="headerName">
                                                        </th>
                                                    </template>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-if="Array.isArray(section.content[0])"> {{-- Matrix --}}
                                                <template x-for="(row, rowIndex) in section.content" :key="rowIndex">
                                                    <tr>
                                                        <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300"
                                                                x-text="typeof cell === 'number' ? cell.toFixed(5) : cell">
                                                            </td>
                                                        </template>
                                                    </tr>
                                                </template>
                                            </template>
                                            <template x-if="!Array.isArray(section.content[0]) && section.content.length > 0"> {{-- Single array --}}
                                                <tr>
                                                    <template x-for="(cell, cellIndex) in section.content" :key="cellIndex">
                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-300"
                                                            x-text="typeof cell === 'number' ? cell.toFixed(5) : cell">
                                                        </td>
                                                    </template>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            <template x-if="section.type === 'scalar' || (Array.isArray(section.content) && section.content.length === 0)">
                                <p class="text-sm font-bold" x-text="section.content !== null && section.content !== undefined ? section.content.toString() : 'N/A'"></p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @push('scripts')
    <script type="module">
        import Alpine from 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/+esm'
        import collapse from 'https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.9/+esm'

        Alpine.plugin(collapse)
        window.Alpine = Alpine
        Alpine.start()
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mabacModalController', () => ({ // This name matches x-data above
                isOpen: false,
                openItem: null, // For accordion state
                modalTitle: '',
                criteriaHeaders: ['Variasi Menu', 'Harga Menu', 'Kecepatan WiFi', 'Mushola', 'Jarak Kafe'],
                sections: [],

                openModal(detail) {
                    // console.log('History Page - Modal detail received:', detail);
                    this.modalTitle = `${detail.recommendationDateTime} – ${detail.title}`;
                    this.sections = [{
                            title: `Normalized Matrix (${detail.title})`,
                            content: detail.normalizedData || [],
                            type: 'array'
                        },
                        {
                            title: `Weighted Matrix (${detail.title})`,
                            content: detail.weightedData || [],
                            type: 'array'
                        },
                        {
                            title: 'Border Approximation Matrix (Global)',
                            content: detail.borderApproximation || [],
                            type: 'array'
                        },
                        {
                            title: `Distance to Border Approximation Matrix (${detail.title})`,
                            content: detail.distanceData || [],
                            type: 'array'
                        },
                        {
                            title: 'Ranking Score',
                            content: detail.rankingScoreValue !== undefined ? parseFloat(detail.rankingScoreValue).toFixed(5) : 'N/A',
                            type: 'scalar'
                        }
                    ];
                    this.isOpen = true;
                    this.openItem = null; // Reset accordion state when modal opens
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
