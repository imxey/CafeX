<x-app-layout>
    <div class="py-10">
        <div class="w-full sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Item 1 -->
                <x-recommendation-card>
                    <x-slot name="rank">1</x-slot>
                    <x-slot name="title">Mostly</x-slot>
                    <x-slot name="image">/images/dashboard.png</x-slot>
                    <x-slot name="address">Jl. Taufiqurrahman No.57A, Beji Tim., Kecamatan Beji, Kota Depok, Jawa Barat
                        16422</x-slot>
                    <x-slot name="openTime">8 am</x-slot>
                    <x-slot name="closeTime">10 pm</x-slot>
                </x-recommendation-card>

                <!-- Item 2 -->
                <x-recommendation-card>
                    <x-slot name="rank">2</x-slot>
                    <x-slot name="title">Kedai kopi Forji</x-slot>
                    <x-slot name="image">/images/dashboard.png</x-slot>
                    <x-slot name="address">Jl. Kabel No.01, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat
                        16425</x-slot>
                    <x-slot name="openTime">9 am</x-slot>
                    <x-slot name="closeTime">9 pm</x-slot>
                </x-recommendation-card>

                <!-- Item 3 -->
                <x-recommendation-card>
                    <x-slot name="rank">3</x-slot>
                    <x-slot name="title">Suar Ruang Coffee</x-slot>
                    <x-slot name="image">/images/dashboard.png</x-slot>
                    <x-slot name="address">Jl. H. Amat No.2, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425</x-slot>
                    <x-slot name="openTime">10 am</x-slot>
                    <x-slot name="closeTime">8 pm</x-slot>
                </x-recommendation-card>
            </div>
        </div>
    </div>
</x-app-layout>