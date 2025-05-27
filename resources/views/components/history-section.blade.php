@props(['date', 'preferences']) {{-- 'preferences' will be a Collection of Preference models --}}

<div class="flex flex-col gap-4 w-full md:w-3/4 lg:w-1/2 mt-8 px-4"> {{-- Added responsive width and padding --}}
    <h3 class="text-black font-semibold text-lg">{{ $date }}</h3>

    @if($preferences && $preferences->count() > 0)
    @foreach ($preferences as $preference)
    {{--
                $preference is an instance of your App\Models\Preference model.
                So $preference->created_at is a Carbon instance.
            --}}
    <button
        type="button" {{-- Good practice for buttons not submitting forms --}}
        class="select-none cursor-pointer hover:bg-[#616465] hover:text-white text-black border-2 border-[#616465] px-8 py-3 flex justify-between items-center rounded-md shadow-sm" {{-- Added rounded and shadow --}}
        {{-- For modal functionality, you'll likely use JavaScript (e.g., Alpine.js) --}}
        {{-- Example with Alpine.js (assuming you have a modal component/logic) --}}
        {{-- x-data @click="$dispatch('open-preference-modal', { preferenceId: {{ $preference->id }} })" --}}
        {{-- Or a simple JS function: --}}
        onclick="showPreferenceDetails({{ $preference->id }}, '{{ $preference->created_at->format('H:i:s') }}')">
        <p class="text-md font-semibold">{{ $preference->created_at->format('H:i:s') }}</p>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
        </svg>
    </button>
    @endforeach
    @else
    {{-- This case should ideally not be hit if controller logic ensures preferences are passed only if they exist for the date --}}
    <p class="text-sm text-gray-500">No entries for this date.</p>
    @endif
</div>

{{-- Add a placeholder for your modal and JS function if not using Alpine.js or similar --}}

@once
@push('scripts')
<script>
    function showPreferenceDetails(preferenceId, time) {
        // This is a very basic example.
        // You'd typically fetch full preference data via AJAX if needed,
        // then populate and show your modal.
        alert(`Details for preference ID: ${preferenceId} at ${time}\nMenu: [Fetch Data]\nPrice: [Fetch Data]... etc.\n\nImplement your modal logic here.`);

        // Example:
        // 1. Get modal element
        // const modal = document.getElementById('myPreferenceModal');
        // 2. Populate modal content (e.g., by fetching data with `fetch` API)
        // fetch(`/api/preferences/${preferenceId}`)
        //   .then(response => response.json())
        //   .then(data => {
        //      // document.getElementById('modal-time').textContent = time;
        //      // document.getElementById('modal-menu').textContent = data.preference_menu;
        //      // ... populate other fields
        //      // modal.style.display = 'block'; // Show modal
        //   });
    }
</script>
@endpush
@endonce
