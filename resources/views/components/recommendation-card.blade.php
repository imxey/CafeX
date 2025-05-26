<div class="flex flex-col">
    <div class="flex justify-end w-[320px] mx-auto">
        <div
            class="p-3 bg-red-500 rounded-xl rounded-b-none border-2 border-black border-b-0 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] text-white text-lg">
            <h1>#{{ $rank }} Recomended</h1>
        </div>
    </div>
    <div
        class="w-[320px] mx-auto bg-[#FAF1DC] shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] p-6 rounded-md rounded-tr-none border-2 border-black flex flex-col items-center gap-5 h-full justify-between">
        <h1 class="text-5xl font-[800] text-center mb-4">{{$title}}</h1>

        <img src="{{$image}}" alt="Mostly" class="w-full h-48 object-cover rounded-md mb-4">

        <p class="text-center text-mdmb-2">
            {{ $address }}
        </p>

        <p class="text-center text-md mb-4">
            Open : {{ $openTime }} - {{ $closeTime }}
        </p>

        <div class="flex justify-center gap-4">
            <button
                class=" text-white select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3  font-semibold fs-3 min-w-[15vw]">Get
                Directions</button>
        </div>
    </div>
</div>