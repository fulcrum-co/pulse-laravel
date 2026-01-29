<x-layouts.dashboard title="Strategy">
    <x-slot name="actions">
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">
                <x-icon name="calendar" class="w-4 h-4 inline mr-1" />
                {{ $strategy->start_date->format('n/j/Y') }} - {{ $strategy->end_date->format('n/j/Y') }}
            </span>
            <a href="{{ route('strategies.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Add Entry
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <livewire:strategy-header :strategy="$strategy" />

        {{-- Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('strategies.show', ['strategy' => $strategy, 'view' => 'planner']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'planner' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Planner
                </a>
                <a href="{{ route('strategies.show', ['strategy' => $strategy, 'view' => 'timeline']) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'timeline' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Timeline
                </a>
            </nav>
        </div>

        {{-- Content based on view --}}
        @if($view === 'planner')
            <livewire:strategy-planner :strategy="$strategy" />
        @else
            <livewire:strategy-timeline :strategy="$strategy" />
        @endif

        {{-- Survey Assignment Modal --}}
        <livewire:survey-assignment-modal />

        {{-- Push Strategy Modal --}}
        <livewire:push-strategy-modal :strategy="$strategy" />
    </div>
</x-layouts.dashboard>
