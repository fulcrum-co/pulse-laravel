@props([
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'bg-white border border-gray-200 rounded-xl' .
               ($padding ? ' p-6' : '') .
               ($hover ? ' hover:shadow-lg transition-shadow cursor-pointer' : '')
]) }}>
    {{ $slot }}
</div>
