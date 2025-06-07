@props(['value','title'])
@php
    switch ($value) {
        case 1:
            $width = 10;
            break;
        case 2:
            $width = 30;
            break;
        case 3:
            $width = 50;
            break;
        case 4:
            $width = 70;
            break;
        case 5:
            $width = 100;
            break;
    }
    $weights = [
        '1' => 'Sangat Tidak Penting',
        '2' => 'Tidak Penting',
        '3' => 'Cukup',
        '4' => 'Penting',
        '5' => 'Sangat Penting',
    ];
@endphp

<h1 class="text-lg font-semibold my-5 ">{{ $title }}</h1>
<div class="relative w-full">
    <!-- Garis Latar Belakang (Abu-abu) -->
    <div class="absolute left-0 right-0 top-3 lg:top-5 h-0.5 bg-gray-200 w-full"></div>

    <!-- Garis Progress (Indigo) -->
    <div class="absolute left-0 top-3 lg:top-5 h-0.5 bg-indigo-600 z-0" style="width: {{ $width }}%"></div>

    <!-- Item-item -->
    <div
        class="grid grid-cols-5 justify-center w-full text-xs text-gray-900 font-medium sm:text-base gap-10 relative z-10">
        @foreach ($weights as $key => $weight)
            @if ($key == $value)
                <div class="block whitespace-nowrap text-center text-indigo-600">
                    <span
                        class="w-6 h-6 bg-indigo-50 border-2 border-indigo-600 rounded-full flex justify-center items-center mx-auto mb-3 text-sm text-indigo-600 lg:w-10 lg:h-10">
                        {{ $key }}
                    </span>
                    {{ $weight }}
                </div>
            @elseif ($key < $value)
                <div class="block whitespace-nowrap text-center text-indigo-600">
                    <span
                        class="w-6 h-6 bg-indigo-600 border-2 border-transparent rounded-full flex justify-center items-center mx-auto mb-3 text-sm text-white lg:w-10 lg:h-10">
                        {{ $key }}
                    </span>
                    {{ $weight }}
                </div>
            @else
                <div class="block whitespace-nowrap text-center">
                    <span
                        class="w-6 h-6 bg-gray-50 border-2 border-gray-200 rounded-full flex justify-center items-center mx-auto mb-3 text-sm lg:w-10 lg:h-10">
                        {{ $key }}
                    </span>
                    {{ $weight }}
                </div>
            @endif
        @endforeach
    </div>
</div>