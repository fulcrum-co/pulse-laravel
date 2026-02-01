<x-layouts.dashboard title="Plan">
    <x-slot name="actions">
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">
                <x-icon name="calendar" class="w-4 h-4 inline mr-1" />
                {{ $plan->start_date->format('n/j/Y') }} - {{ $plan->end_date->format('n/j/Y') }}
            </span>

            <a href="{{ route('plans.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Add Entry
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <livewire:plan-header :plan="$plan" />

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                @if($plan->isOkrStyle())
                    {{-- OKR-style tabs --}}
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'goals']) }}"
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'goals' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Goals
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'milestones']) }}"
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'milestones' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Milestones
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'progress']) }}"
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'progress' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Progress
                    </a>
                @else
                    {{-- Traditional plan tabs --}}
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'planner']) }}"
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'planner' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Planner
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'timeline']) }}"
                       class="py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'timeline' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Timeline
                    </a>
                @endif
            </nav>
        </div>

        {{-- Content --}}
        @if($plan->isOkrStyle())
            @if($view === 'goals')
                <livewire:goal-planner :plan="$plan" />
            @elseif($view === 'milestones')
                <livewire:milestone-tracker :plan="$plan" />
            @elseif($view === 'progress')
                <livewire:progress-updates :plan="$plan" />
            @else
                <livewire:goal-planner :plan="$plan" />
            @endif
        @else
            @if($view === 'planner')
                <livewire:plan-planner :plan="$plan" />
            @elseif($view === 'timeline')
                <livewire:plan-timeline :plan="$plan" />
            @else
                <livewire:plan-planner :plan="$plan" />
            @endif
        @endif

        {{-- Modals --}}
        <livewire:survey-assignment-modal />
        <livewire:push-plan-modal :plan="$plan" />
    </div>
</x-layouts.dashboard>
