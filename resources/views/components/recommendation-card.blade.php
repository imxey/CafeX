@props([
'rank',
'title',
'image',
'address',
'maps',
'openTime',
'closeTime',
'normalizedData' => [],
'weightedData' => [],
'borderApproximation' => [],
'distanceData' => [],
'rankingScoreValue' => 0,
'recommendationDateTime'
])

<div class="flex flex-col" x-data="{
    open: false,
    openItem: null,
    // Definisikan nama header kriteria di sini
    criteriaHeaders: ['Variasi Menu', 'Harga Menu', 'Kecepatan WiFi', 'Mushola', 'Jarak Kafe'],
    sections: [
        { title: 'Normalized Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($normalizedData) }}, type: 'array' },
        { title: 'Weighted Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($weightedData) }}, type: 'array' },
        { title: 'Border Approximation Matrix (Global)', content: {{ json_encode($borderApproximation) }}, type: 'array' },
        { title: 'Distance to Border Approximation Matrix (Baris untuk {{ addslashes($title) }})', content: {{ json_encode($distanceData) }}, type: 'array' },
        { title: 'Ranking Score', content: {{ json_encode(round($rankingScoreValue, 5)) }}, type: 'scalar' }
    ]
}">
    <!-- Ribbon -->
    <div class="flex justify-end w-full max-w-[320px] mx-auto">
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
                onerror="this.onerror=null;this.src='/images/default-cafe.png';" />
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
            <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer"
                class="text-white select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3 font-semibold text-base min-w-[150px] text-center rounded">
                Get Directions
            </a>
        </div>
    </div>

    <!-- MODAL -->
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-75 p-4"
        x-show="open"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        style="display: none;">

        <div class="bg-white rounded-lg w-full max-w-2xl p-6 border-black border-2 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] max-h-[90vh] overflow-y-auto"
            @click.away="open = false" x-transition:enter="transition transform ease-out duration-300"
            x-transition:enter-start="scale-75 opacity-0" x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition transform ease-in duration-200"
            x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-75 opacity-0">
            <div class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4">
                <h2 class="text-xl font-semibold">{{ $recommendationDateTime }} – {{ $title }}</h2>
                <button @click="open = false"
                    class="bg-red-600 w-8 h-8 rounded-full flex items-center justify-center text-white hover:bg-red-700 text-xl font-medium transition-colors">×</button>
            </div>

            <div class="space-y-2">
                <template x-for="(section, index) in sections" :key="index">
                    <div class="border border-gray-300 rounded overflow-hidden">
                        <button @click="openItem === index ? openItem = null : openItem = index"
                            class="w-full flex items-center justify-between text-left px-4 py-3 font-medium bg-gray-100 hover:bg-gray-200 transition focus:outline-none">
                            <span x-text="section.title" class="text-gray-800"></span>
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
                        <div x-show="openItem === index" x-collapse
                            class="px-4 py-3 text-sm text-gray-700 bg-white">

                            <template x-if="section.type === 'array' && Array.isArray(section.content) && section.content.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <template x-for="(headerName, colIndex) in criteriaHeaders" :key="colIndex">
                                                    <template x-if="(Array.isArray(section.content[0]) && colIndex < section.content[0].length) || (!Array.isArray(section.content[0]) && colIndex < section.content.length)">
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300"
                                                            x-text="headerName">
                                                        </th>
                                                    </template>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-if="Array.isArray(section.content[0])">
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
                                            <template x-if="!Array.isArray(section.content[0])">
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
                                <p class="text-sm font-bold" x-text="section.content.toString()"></p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
