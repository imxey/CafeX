<div class="flex flex-col" x-data="{ open: false, openItem: null }">
    <!-- Ribbon -->
    <div class="flex justify-end w-[320px] mx-auto">
        <div
            class="p-3 bg-red-500 rounded-xl rounded-b-none border-2 border-black border-b-0 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] text-white text-lg">
            <h1>#{{ $rank }} Recommendations</h1>
        </div>
    </div>

    <!-- Card -->
    <div
        class="w-[320px] mx-auto bg-[#FAF1DC] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] p-6 rounded-md rounded-tr-none border-2 border-black flex flex-col items-center gap-5 h-full justify-between">
        <h1 class="text-4xl font-[800] text-center mb-4">{{ $title }}</h1>

        <div class="relative w-full h-48 group rounded-md overflow-hidden">
            <img src="{{ $image }}" alt="{{ $title }}"
                class="w-full h-full object-cover transition duration-300 group-hover:brightness-50" />
            <div
                class="absolute inset-0 flex items-end justify-center text-white font-semibold border border-white rounded-md opacity-0 group-hover:opacity-100 transition duration-300">
                <button @click="open = true"
                    class="p-2 border-2 border-white mb-2 rounded-md hover:bg-opacity-70 transition duration-300 hover:text-black hover:opacity-50 hover:bg-white">
                    Show Mabac Score
                </button>
            </div>
        </div>

        <p class="text-center text-md">{{ $address }}</p>
        <p class="text-center text-md mb-4">Open : {{ $openTime }} - {{ $closeTime }}</p>

        <div class="flex justify-center gap-4">
            <button
                class="text-white select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3 font-semibold fs-3 min-w-[15vw]">
                Get Directions
            </button>
        </div>
    </div>

    <!-- MODAL -->
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50" x-show="open"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-md w-full max-w-xl p-6 border-black border-b-0 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]"
            @click.away="open = false" x-transition:enter="transition transform ease-out duration-300"
            x-transition:enter-start="scale-75 opacity-0" x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition transform ease-in duration-200"
            x-transition:leave-start="scale-100 opacity-100" x-transition:leave-end="scale-75 opacity-0">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-semibold">10-5-2025 – Mostly</h2>
                <button @click="open = false"
                    class="bg-[#730205] w-5 h-5 rounded-full flex items-center justify-center text-white hover:text-white-800 text-3xl p-5 font-medium hover:opacity-80">×</button>
            </div>

            <div class="space-y-2">
                <template x-for="(section, index) in [
                    { title: 'Normalized Matrix', content: '...' },
                    { title: 'Weighted Matrix', content: '...' },
                    { title: 'Border Approximation Matrix', content: '...' },
                    { title: 'Distance to Border Approximation Matrix', content: '...' },
                    { title: 'Ranking Score', content: '0.35132' }
                ]" :key="index">
                    <div class="border rounded overflow-hidden">
                        <button @click="openItem === index ? openItem = null : openItem = index"
                            class="w-full flex items-center justify-between text-left px-4 py-2 font-medium bg-gray-100 hover:bg-gray-200 transition">
                            <span x-text="section.title"></span>
                            <template x-if="openItem === index">
                                <svg width="20" height="13" viewBox="0 0 26 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24.5 13.75L13 2.25L1.5 13.75" stroke="#1E1E1E" stroke-width="3"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </template>
                            <template x-if="openItem !== index">
                                <svg width="20" height="13" viewBox="0 0 26 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24.5 2.25L13 13.75L1.5 2.25" stroke="#1E1E1E" stroke-width="3"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </template>
                        </button>
                        <div x-show="openItem === index" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="max-h-0 opacity-0" x-transition:enter-end="max-h-40 opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="max-h-40 opacity-100" x-transition:leave-end="max-h-0 opacity-0"
                            class="px-4 py-2 text-sm text-gray-700 overflow-hidden">
                            <span x-text="section.content"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>