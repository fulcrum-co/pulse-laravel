<x-layouts.dashboard title="Alerts">
    <x-slot name="actions">
        <a href="{{ route('alerts.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            Create Alert
        </a>
    </x-slot>

    @livewire('alerts.alerts-index')
</x-layouts.dashboard>
