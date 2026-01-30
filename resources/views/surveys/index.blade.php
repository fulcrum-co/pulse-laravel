<x-layouts.dashboard title="Surveys">
    <x-slot name="actions">
        <a href="{{ route('surveys.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Create Survey
        </a>
    </x-slot>

    <livewire:survey.survey-list />
</x-layouts.dashboard>
