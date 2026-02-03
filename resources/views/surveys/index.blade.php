<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('survey_plural') }}">
    <x-slot name="actions">
        <a href="{{ route('surveys.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            @term('create_action') @term('survey_singular')
        </a>
    </x-slot>

    <livewire:survey.survey-list />
</x-layouts.dashboard>
