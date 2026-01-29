@props([
    'color' => 'gray',
])

@php
$colorClasses = match($color) {
    'gray' => 'bg-gray-100 text-gray-800',
    'green' => 'bg-green-100 text-green-800',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'red' => 'bg-red-100 text-red-800',
    'orange' => 'bg-orange-100 text-orange-800',
    'purple' => 'bg-purple-100 text-purple-800',
    'blue' => 'bg-blue-100 text-blue-800',
    default => 'bg-gray-100 text-gray-800',
};
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $colorClasses"
]) }}>
    {{ $slot }}
</span>
