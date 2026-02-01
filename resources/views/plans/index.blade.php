<x-layouts.dashboard title="Plans">
    <x-slot name="actions">
        <a href="{{ route('plans.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Add Plan
        </a>
    </x-slot>

    <livewire:plan-list />
</x-layouts.dashboard>
