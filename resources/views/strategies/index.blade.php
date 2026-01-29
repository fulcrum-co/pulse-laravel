<x-layouts.dashboard title="Strategy">
    <x-slot name="actions">
        <a href="{{ route('strategies.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Add Entry
        </a>
    </x-slot>

    <livewire:strategy-list />
</x-layouts.dashboard>
