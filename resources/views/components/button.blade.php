<button {{ $attributes->merge(['type' => 'submit', 'class' => 'select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3 rounded-xl font-semibold fs-3']) }}>
    {{ $slot }}
</button>
