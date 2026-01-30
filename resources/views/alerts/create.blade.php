<x-layouts.dashboard title="Create Alert">
    <x-slot name="actions">
        <form action="{{ route('alerts.store') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="name" value="Untitled Workflow">
            <input type="hidden" name="mode" value="advanced">
            <x-button type="submit" variant="secondary">
                <x-icon name="squares-2x2" class="w-4 h-4 mr-2" />
                Visual Editor
            </x-button>
        </form>
        <a href="{{ route('alerts.index') }}">
            <x-button variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Back to Alerts
            </x-button>
        </a>
    </x-slot>

    @livewire('alerts.alert-wizard')
</x-layouts.dashboard>
