<x-layouts.dashboard title="Plan">
    <x-slot name="actions">
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">
                <x-icon name="calendar" class="w-3.5 h-3.5 inline mr-1" />
                {{ $plan->start_date->format('n/j/Y') }} - {{ $plan->end_date->format('n/j/Y') }}
            </span>

            <a href="{{ route('plans.create') }}" class="inline-flex items-center px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-xs font-medium hover:bg-pulse-orange-600">
                <x-icon name="plus" class="w-3.5 h-3.5 mr-1" />
                Add Entry
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        {{-- Header --}}
        <livewire:plan-header :plan="$plan" />

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex gap-6">
                @if($plan->isOkrStyle())
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'goals']) }}"
                       class="py-2 border-b-2 text-xs font-medium {{ $view === 'goals' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Goals
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'milestones']) }}"
                       class="py-2 border-b-2 text-xs font-medium {{ $view === 'milestones' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Milestones
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'progress']) }}"
                       class="py-2 border-b-2 text-xs font-medium {{ $view === 'progress' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Progress
                    </a>
                @else
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'planner']) }}"
                       class="py-2 border-b-2 text-xs font-medium {{ $view === 'planner' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Planner
                    </a>
                    <a href="{{ route('plans.show', ['plan' => $plan, 'view' => 'timeline']) }}"
                       class="py-2 border-b-2 text-xs font-medium {{ $view === 'timeline' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
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
