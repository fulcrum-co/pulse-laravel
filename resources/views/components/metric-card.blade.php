@props([
    'label',
    'value',
    'change' => null,
    'changeType' => 'positive',
    'color' => 'green',
])

@php
$colorClasses = match($color) {
    'green' => 'bg-green-500',
    'yellow' => 'bg-yellow-500',
    'red' => 'bg-red-500',
    'orange' => 'bg-pulse-orange-500',
    'purple' => 'bg-pulse-purple-600',
    default => 'bg-green-500',
};
@endphp

<x-card padding="true">
    <div class="h-1 {{ $colorClasses }} -mt-6 -mx-6 mb-6 rounded-t-xl"></div>

    <div class="text-sm text-gray-600 mb-1">{{ $label }}</div>

    <div class="flex items-baseline justify-between">
        <span class="text-5xl font-semibold text-gray-900">{{ $value }}</span>

        @if($change)
        <div class="text-right">
            <div class="text-sm font-medium {{ $changeType === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                {{ $changeType === 'positive' ? '↑' : '↓' }} {{ $change }}
            </div>
            <div class="text-xs text-gray-500">@term('from_last_month_label')</div>
        </div>
        @endif
    </div>
</x-card>
