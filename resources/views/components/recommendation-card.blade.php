@props([
'rank',
'title',
'image',
'address',
'openTime',
'closeTime',
'normalizedData' => [],
'weightedData' => [],
'borderApproximation' => [],
'distanceData' => [],
'rankingScoreValue' => 0
])

<div class="flex flex-col" x-data="{
    open: false,
    openItem: null,
    sections: [
        { title: 'Normalized Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($normalizedData) }} },
        { title: 'Weighted Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($weightedData) }} },
        { title: 'Border Approximation Matrix (Global)', content: {{ json_encode($borderApproximation) }} },
        { title: 'Distance to Border Approximation Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($distanceData) }} },
        { title: 'Ranking Score', content: {{ json_encode(round($rankingScoreValue, 5)) }} } // round agar lebih rapi
    ]
}">
    <!-- Ribbon -->
    <div class="flex justify-end w-full max-w-[320px] mx-auto"> {{-- w-full max-w agar responsif --}}
        <div
            class="p-3 bg-red-500 rounded-xl rounded-b-none border-2 border-black border-b-0 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] text-white text-lg">
            <h1>#{{ $rank }} Recommendations</h1>
        </div>
    </div>

    <!-- Card -->
    <div
        class="w-full max-w-[320px] mx-auto bg-[#FAF1DC] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] p-6 rounded-md rounded-tr-none border-2 border-black flex flex-col items-center gap-5 h-full justify-between">
        <h1 class="text-4xl font-[800] text-center mb-4">{{ $title }}</h1>

        <div class="relative w-full h-48 group rounded-md overflow-hidden">
            <img src="{{ $image }}" alt="{{ $title }}"
                class="w-full h-full object-cover transition duration-300 group-hover:brightness-50"
                onerror="this.onerror=null;this.src='/images/default-cafe.png';" {{-- Fallback image --}} />
            <div
                class="absolute inset-0 flex items-end justify-center text-white font-semibold opacity-0 group-hover:opacity-100 transition duration-300">
                <button @click="open = true"
                    class="p-2 border-2 border-white mb-2 rounded-md hover:bg-opacity-70 transition duration-300 hover:text-black hover:bg-white">
                    Show Mabac Score
                </button>
            </div>
        </div>

        <p class="text-center text-md">{{ $address }}</p>
        <p class="text-center text-md mb-4">Open : {{ $openTime }} - {{ $closeTime }}</p>

        <div class="flex justify-center gap-4">
            <button
                class="text-white select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3 font-semibold text-base min-w-[150px]"> {{-- Sesuaikan min-w --}}
                Get Directions
            </button>
        </div>
    </div>

    <!-- MODAL -->
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-75 p-4" {{-- bg-opacity-75 dan p-4 --}}
        x-show="open"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        style="display: none;" {{-- Mencegah FOUC --}}>

        <div class="bg-white rounded-lg w-full max-w-xl p-6 border-black border-2 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] max-h-[90vh] overflow-y-auto" {{-- rounded-lg, border-2, max-h, overflow-y --}}
            @click.away="open = false" x-transition:enter="transition transform ease-out duration-300"
            x-transition:enter-start="scale-75 opacity-0" x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition transform ease-in duration-200"
            x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-75 opacity-0">
            <div class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4"> {{-- border-gray-300, pb-3 --}}
                <h2 class="text-xl font-semibold">{{ now()->format('d-m-Y') }} – {{ $title }}</h2>
                <button @click="open = false"
                    class="bg-red-600 w-8 h-8 rounded-full flex items-center justify-center text-white hover:bg-red-700 text-xl font-medium transition-colors">×</button> {{-- Ukuran dan style tombol close --}}
            </div>

            <div class="space-y-2">
                <template x-for="(section, index) in sections" :key="index">
                    <div class="border border-gray-300 rounded overflow-hidden"> {{-- border-gray-300 --}}
                        <button @click="openItem === index ? openItem = null : openItem = index"
                            class="w-full flex items-center justify-between text-left px-4 py-3 font-medium bg-gray-100 hover:bg-gray-200 transition focus:outline-none"> {{-- py-3, focus --}}
                            <span x-text="section.title" class="text-gray-800"></span> {{-- text-gray-800 --}}
                            <template x-if="openItem === index">
                                <svg class="w-5 h-5 text-gray-600 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </template>
                            <template x-if="openItem !== index">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </template>
                        </button>
                        <div x-show="openItem === index" x-collapse {{-- x-collapse untuk animasi lebih smooth --}}
                            class="px-4 py-3 text-sm text-gray-700 bg-white"> {{-- bg-white --}}
                            <pre class="whitespace-pre-wrap break-all text-xs" x-text="typeof section.content === 'object' ? JSON.stringify(section.content, null, 2) : section.content"></pre> {{-- text-xs --}}
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
