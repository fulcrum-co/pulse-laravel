<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('report_plural') }}">
    <x-slot name="actions">
        <a href="{{ route('reports.create') }}">
            <x-button variant="primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                @term('create_action') @term('report_singular')
            </x-button>
        </a>
    </x-slot>

    <livewire:report-list />
</x-layouts.dashboard>
