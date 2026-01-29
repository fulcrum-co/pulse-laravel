@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
])

<div>
    @if($label)
    <label for="{{ $attributes->get('id') }}" class="block text-sm font-medium text-gray-700 mb-1.5">
        {{ $label }}
    </label>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'w-full px-4 py-2.5 border rounded-lg text-sm transition focus:outline-none focus:ring-2 ' .
                       ($error
                           ? 'border-red-300 focus:ring-red-100 focus:border-red-500'
                           : 'border-gray-300 focus:ring-pulse-orange-100 focus:border-pulse-orange-500')
        ]) }}
    />

    @if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
