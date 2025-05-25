<div class="p-2 min-h-[calc(100vh-1rem)] px-10 min-w-full mx-auto">

    <!-- Navigasi -->
    <div class="flex my-4">
        @if ($currentQuestion > 1)
            <div>
                <p class="text-[#4A4A4A] hover:text-[#919191] cursor-pointer" wire:click="previous">Kembali</p>
            </div>
        @else
            <div>
                <p class="text-[#4A4A4A] hover:text-[#919191] cursor-pointer">KafeX</p>
            </div>
        @endif
    </div>

    <!-- Progress Bar -->
    <div class="bg-white rounded-full h-2.5 mb-4 my-10">
        <div class="bg-[#4A4A4A] h-2.5 rounded-full" style="width: {{ ($currentQuestion / count($questions)) * 100 }}%">
        </div>
    </div>

    <!-- Pertanyaan -->
    <div class="flex flex-col justify-center text-center gap-3 items-center h-full w-full mt-10">
        <h1 class="text-[#4A4A4A] font-bold lg:text-xl w-full">
            Pertanyaan {{ $currentQuestion }}/{{ count($questions) }}
        </h1>

        <div class="w-full px-4">
            <h1 class="text-[#4A4A4A] font-bold lg:text-3xl break-words whitespace-normal">
                {{ $questions[$currentQuestion] }}
            </h1>
        </div>

        <!-- Opsi Jawaban -->
        <div class="w-full gap-10 justify-center flex mt-20">
            @foreach($options as $option => $label)
                <div wire:click="selectAnswer({{ $option }})"
                    class="flex flex-col px-2 py-4 items-center justify-between gap-4 border border-black text-xl font-bold min-w-44 max-w-44 rounded-3xl cursor-pointer transition-all
                            {{ ($answers[$currentQuestion] ?? null) === $option ? 'bg-gray-200 shadow-inner' : 'hover:bg-gray-50 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]' }}">
                    <p class="text-3xl text-gray-600">
                        {{ $option }}
                    </p>

                    <span class="text-gray-600">
                        {{ $label }}
                    </span>

                    <div class="flex items-center">
                        <input type="radio" class="h-5 w-5 text-blue-600 focus:ring-blue-500" disabled
                            @if(($answers[$currentQuestion] ?? null) === $option) checked @endif>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Tombol Aksi -->
    @if($currentQuestion < count($questions))
        <button wire:click="next" wire:loading.attr="disabled"
            class="fixed right-10 bottom-8 bg-[#4A4A4A] hover:bg-[#363535] text-white rounded-3xl px-6 py-3 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <span>Selanjutnya</span>
            <i class="fa-solid fa-arrow-right mt-1"></i>
        </button>
    @else
        <button wire:click="save" wire:loading.attr="disabled"
            class="fixed right-10 bottom-8 bg-[#4A4A4A] hover:bg-[#d18270] text-white rounded-3xl px-6 py-3 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-floppy-disk lg:text-lg"></i>
            <span>Simpan</span>
        </button>
    @endif

</div>