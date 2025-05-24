@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'focus:ring-0 focus:outline-none border-none bg-[rgba(74,74,74,0.4)] p-4 rounded-xl h-12']) !!}>
