@props([
    'status' => 'ready',
    'size' => 'sm', // 'xs', 'sm', 'md'
    'showLabel' => true,
])

@php
    $config = match($status) {
        'pending' => [
            'label' => 'Queued',
            'bg' => 'bg-yellow-100',
            'text' => 'text-yellow-700',
            'icon' => 'clock',
            'animate' => false,
        ],
        'processing' => [
            'label' => 'Processing',
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-700',
            'icon' => 'arrow-path',
            'animate' => true,
        ],
        'needs_embedding' => [
            'label' => 'Indexing',
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-700',
            'icon' => 'sparkles',
            'animate' => true,
        ],
        'stale' => [
            'label' => 'Updating',
            'bg' => 'bg-orange-100',
            'text' => 'text-orange-700',
            'icon' => 'arrow-path',
            'animate' => true,
        ],
        'failed' => [
            'label' => 'Failed',
            'bg' => 'bg-red-100',
            'text' => 'text-red-700',
            'icon' => 'exclamation-circle',
            'animate' => false,
        ],
        default => [
            'label' => 'Ready',
            'bg' => 'bg-green-100',
            'text' => 'text-green-700',
            'icon' => 'check-circle',
            'animate' => false,
        ],
    };

    $sizeClasses = match($size) {
        'xs' => 'text-xs px-1.5 py-0.5',
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-sm px-2.5 py-1',
        default => 'text-xs px-2 py-0.5',
    };

    $iconSize = match($size) {
        'xs' => 'w-3 h-3',
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4 h-4',
        default => 'w-3.5 h-3.5',
    };
@endphp

@if($status !== 'ready')
<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1 rounded-full font-medium {$config['bg']} {$config['text']} {$sizeClasses}"
]) }}>
    <x-icon
        name="{{ $config['icon'] }}"
        class="{{ $iconSize }} {{ $config['animate'] ? 'animate-spin' : '' }}"
    />
    @if($showLabel)
        <span>{{ $config['label'] }}</span>
    @endif
</span>
@endif
