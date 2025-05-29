@props([
    'fill' => '#D1363A',
    'name' => 'Nama Lengkap',
    'height' => 558,
    'image' => 'sample.png'
])

<div class="h-[{{ $height }}px] rounded-[1000px_1000px_0px_0px] flex flex-col justify-center items-center border border-black bg-[{{ $fill }}] shadow-[20px_20px_0px_0px_#000]">
    <h2 class="font-extrabold text-2xl mt-9 pt-5">{{ $name }}</h2>
    <img src="/images/{{ $image }}.png" alt="">
</div>
