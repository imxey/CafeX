<x-app-layout>
    <div class="pt-24">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex overflow-hidden gap-12">
                <x-welcome />
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    fetch("{{ route('save-location') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('📍 Lokasi berhasil disimpan:', data)
                    })
                    .catch(error => {
                        console.error('⚠️ Error saat simpan lokasi:', error)
                    });
                },
                function (error) {
                    console.error('🚫 Gagal ambil lokasi:', error.message)
                }
            );
        } else {
            console.error('😢 Browser kamu tidak mendukung geolocation.')
        }
    });
</script>
