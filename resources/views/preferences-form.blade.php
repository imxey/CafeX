<x-guest-layout>
    <form method="POST" action="{{ route('preferences.store') }}" class="p-2 min-h-[calc(100vh-1rem)] px-10 min-w-full mx-auto" 
        x-data="{
            currentQuestion: 1,
            answers: {},
            setAnswer(questionId, value) {
                this.answers[questionId] = value;
            },
            isLoading: false,
            getAnswer(questionId) {
                return this.answers[questionId] || null;
            },
        }">
        @csrf
        
        <!-- Input hidden untuk menyimpan jawaban -->
        <template x-for="(answer, index) in Object.entries(answers)" :key="index">
            <input type="hidden" x-bind:name="`answers[${answer[0] - 1}]`" x-bind:value="answer[1]">
        </template>

        <!-- Navigasi -->
        <div class="flex my-4">
            <div x-show="currentQuestion > 1" x-on:click="currentQuestion--">
                <p class="text-[#4A4A4A] hover:text-[#919191] cursor-pointer transition-colors duration-200"
                    x-click="currentQuestion--">Kembali</p>
            </div>
            <div x-show="currentQuestion == 1 ? true : false">
                <p class="text-[#4A4A4A] hover:text-[#919191] cursor-pointer transition-colors duration-200">KafeX</p>
            </div>
        </div>

        @foreach ($questions as $index => $question)
            <!-- Progress Bar -->
            <div class="bg-white rounded-full h-2.5 mb-4 my-10" x-show="currentQuestion == {{ $index }}">
                <div class="bg-[#4A4A4A] h-2.5 rounded-full transition-all duration-500 ease-out"
                    style="width: {{ ($index / count($questions)) * 100 }}%">
                </div>
            </div>

            <!-- Pertanyaan -->
            <div class="flex flex-col justify-center text-center gap-3 items-center h-full w-full mt-10" x-show="currentQuestion == {{ $index }}">
                <h1 class="text-[#4A4A4A] font-bold lg:text-xl w-full transition-opacity duration-300">
                    Pertanyaan {{ $index }}/{{ count($questions) }}
                </h1>

                <div class="w-full px-4">
                    <h1
                        class="text-[#4A4A4A] font-bold lg:text-3xl break-words whitespace-normal transition-opacity duration-300">
                        {{ $question }}
                    </h1>
                </div>

                <!-- Opsi Jawaban -->
                <div class="w-full gap-10 justify-center flex mt-20" 
                    x-data="{ selectedOption: getAnswer({{ $index }}) }"
                    x-init="selectedOption = getAnswer({{ $index }})">
                    @foreach($options as $option => $label)
                        <div x-on:click="selectedOption = '{{ $option }}'; setAnswer({{ $index }}, '{{ $option }}')"
                            class="flex flex-col px-2 py-4 items-center justify-between gap-4 border border-black text-xl font-bold min-w-44 max-w-44 rounded-3xl cursor-pointer transition-all duration-200 ease-in-out transform"
                            :class="{
                                'bg-gray-200 shadow-inner scale-95': selectedOption === '{{ $option }}',
                                'hover:bg-gray-50 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] hover:scale-105': selectedOption !== '{{ $option }}'
                            }"
                            @click.prevent>
                            <p class="text-3xl text-gray-600 transition-colors duration-200">
                                {{ $option }}
                            </p>

                            <span class="text-gray-600 transition-colors duration-200">
                                {{ $label }}
                            </span>

                            <div class="flex items-center transition-opacity duration-200">
                                <input type="radio" class="h-5 w-5 text-blue-600 focus:ring-blue-500" disabled
                                    :checked="selectedOption === '{{ $option }}'" value="{{ $option }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Tombol Aksi -->
        <template x-if="currentQuestion < {{ count($questions) }}">
            <button type="button" x-on:click="currentQuestion++" 
                class="fixed right-10 bottom-8 bg-[#4A4A4A] hover:bg-[#363535] text-white rounded-3xl px-6 py-3 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105 active:scale-95">
                <span>Selanjutnya</span>
                <i class="fa-solid fa-arrow-right mt-1 transition-transform duration-200 group-hover:translate-x-1"></i>
            </button>
        </template>

        <template x-if="currentQuestion === {{ count($questions) }}">
            <button type="submit" x-on:click="isLoading = true"
                class="fixed right-10 bottom-8 bg-[#4A4A4A] hover:bg-[#363535] text-white rounded-3xl px-6 py-3 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105 active:scale-95"
                x-bind:disabled="Object.keys(answers).length < {{ count($questions) || isLoading }}"
                x-bind:class="{'opacity-50 cursor-not-allowed': isLoading}">
                <template x-if="!isLoading">
                    <span>Selesai</span>
                </template>
                <template x-if="isLoading">
                    <span>Menyimpan...</span>
                </template>
                <i x-show="!isLoading" class="fa-solid fa-check mt-1 transition-transform duration-200 group-hover:translate-x-1"></i>
                <i x-show="isLoading" class="fas fa-spinner fa-spin mt-1"></i>
            </button>
        </template>
    </form>
</x-guest-layout>