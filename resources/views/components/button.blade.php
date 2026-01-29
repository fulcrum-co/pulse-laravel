@props([
    'variant' => 'primary',
    'size' => 'default',
    'type' => 'button',
])

@php
$classes = match($variant) {
    'primary' => 'bg-pulse-orange-500 hover:bg-pulse-orange-600 text-white focus:ring-pulse-orange-100',
    'secondary' => 'bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 focus:ring-gray-100',
    'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700',
    'danger' => 'bg-red-500 hover:bg-red-600 text-white focus:ring-red-100',
    default => 'bg-pulse-orange-500 hover:bg-pulse-orange-600 text-white focus:ring-pulse-orange-100',
};

$sizeClasses = match($size) {
    'small' => 'px-3 py-1.5 text-sm',
    'default' => 'px-4 py-2 text-sm',
    'large' => 'px-6 py-3 text-base',
    default => 'px-4 py-2 text-sm',
};
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-lg font-medium transition-colors focus:outline-none focus:ring-4 disabled:opacity-50 disabled:cursor-not-allowed $classes $sizeClasses"]) }}
>
    {{ $slot }}
</button>
