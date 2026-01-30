<x-layouts.dashboard title="Edit Alert">
    <x-slot name="actions">
        <a href="{{ route('alerts.canvas', $workflowId) }}">
            <x-button variant="secondary">
                <x-icon name="squares-2x2" class="w-4 h-4 mr-2" />
                Visual Editor
            </x-button>
        </a>
        <a href="{{ route('alerts.index') }}">
            <x-button variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Back to Alerts
            </x-button>
        </a>
    </x-slot>

    @livewire('alerts.alert-wizard', ['workflowId' => $workflowId])
</x-layouts.dashboard>
