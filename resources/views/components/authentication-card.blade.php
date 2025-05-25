<div class="bg-white p-[2rem] gap-10 w-[80%] min-h-[80vh] rounded-xl flex">
    @if (isset($left) && isset($right))
        <div class="w-1/2 flex flex-col justify-center items-center">
            {{ $left }}
        </div>
        <div class="w-1/2 flex flex-col justify-center items-center">
            {{ $right }}
        </div>
    @else
        <div class="w-full flex flex-col justify-center items-center">
            {{ $slot }}
        </div>
    @endif
</div>
