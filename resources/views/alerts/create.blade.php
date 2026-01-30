<x-layouts.dashboard title="Create Alert">
    <x-slot name="actions">
        <a href="{{ route('alerts.index') }}">
            <x-button variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Back to Alerts
            </x-button>
        </a>
    </x-slot>

    @livewire('alerts.alert-wizard')
</x-layouts.dashboard>
