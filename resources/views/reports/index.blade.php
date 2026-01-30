<x-layouts.dashboard title="Reports">
    <x-slot name="actions">
        <a href="{{ route('reports.create') }}">
            <x-button variant="primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Create Report
            </x-button>
        </a>
    </x-slot>

    <livewire:report-list />
</x-layouts.dashboard>
