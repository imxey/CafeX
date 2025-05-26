@props([
    'fill' => '#D1363A',
    'name' => 'Nama Lengkap',
    'height' => 558,
    'image' => '/images/sample.png'
])

<svg height="{{ $height }}" viewBox="0 0 515 558" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-1">
    <g filter="url(#filter0_d)">
        <path d="M0 247.5C0 110.81 110.81 0 247.5 0C384.19 0 495 110.81 495 247.5V596H0V247.5Z" fill="{{ $fill }}"/>
        <path d="M247.5 0.5C383.914 0.5 494.5 111.086 494.5 247.5V595.5H0.5V247.5C0.500008 111.086 111.086 0.5 247.5 0.5Z" stroke="black"/>
    </g>
    <foreignObject x="120" y="80" width="250" height="100">
        <div xmlns="http://www.w3.org/1999/xhtml" class="text-center text-black text-3xl font-extrabold">
            <p style="font-weight: 900; color: black;">{{ $name }}</p>
        </div>
    </foreignObject>
    <foreignObject x="28" y="100" width="500" height="500">
        <div xmlns="http://www.w3.org/1999/xhtml" class="flex justify-center items-center">
            <img src="{{ $image }}" alt="{{ $name }}" style="width: 600px; height: 500px;"/>
        </div>
    </foreignObject>
    <defs>
        <filter id="filter0_d" x="0" y="0" width="515" height="616" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dx="20" dy="20"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
        </filter>
    </defs>
</svg>
