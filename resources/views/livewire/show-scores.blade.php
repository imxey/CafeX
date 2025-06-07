<x-action-section>
    <x-slot name="title">
        {{ __('Weight Scores') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Set the weight scores for each criterion of the MABAC.') }}
    </x-slot>

    <x-slot name="content">
        <div class="relative">
            @foreach ($preferences as $key => $value)
                <x-scores-stepper title="{{ $key }}" :value="$value" />
            @endforeach
            <div class="flex justify-end mt-8">
                <x-button type="button" wire:loading.attr="disabled" wire:navigate
                    href="{{ route('questionnaire') }}">
                    {{ __('Edit Scores') }}
                </x-button>
            </div>
        </div>
    </x-slot>

</x-action-section>