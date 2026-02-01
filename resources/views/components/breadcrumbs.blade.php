@props(['items' => []])

@if(count($items) > 0)
<nav class="flex items-center text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    <x-icon name="chevron-right" class="w-4 h-4 mx-2 text-gray-400" />
                @endif

                @if(isset($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}"
                       class="text-gray-500 hover:text-gray-700 transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-900 font-medium">
                        {{ Str::limit($item['label'], 40) }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
