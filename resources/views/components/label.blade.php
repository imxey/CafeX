@props(['value'])

<label {{ $attributes->merge(['class' => 'font-semibold fs-3']) }}>
    {{ $value ?? $slot }}
</label>