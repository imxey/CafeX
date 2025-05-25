@props(['option', 'label', 'tempAnswer', 'isActive'])

@if ($isActive)

@endif

<div x-data="{ optionValue: {{ $option }} }" @class([ 'shadow-[10px_10px_0px_0px_rgba(0,0,0,1)]'=> $isActive,
    ])class=" flex flex-col px-2 py-4 items-center justify-between gap-4 border
    border-black text-xl font-bold min-w-44 max-w-44 rounded-3xl hover:bg-gray-50 cursor-pointer transition-all">

    <p class="text-3xl text-gray-600">
        {{ $option }}
    </p>

    <span class="text-gray-600">
        {{ $label }}
    </span>

    <div class="flex items-center">
        <input type="radio" :checked="tempAnswer === optionValue" class="h-5 w-5 text-blue-600 focus:ring-blue-500">
    </div>
</div>