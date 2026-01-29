@props([
    'active' => false,
    'href' => '#',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ' .
                   ($active
                       ? 'bg-orange-50 text-pulse-orange-600'
                       : 'text-gray-700 hover:bg-gray-50')
    ]) }}
>
    {{ $slot }}
</a>
