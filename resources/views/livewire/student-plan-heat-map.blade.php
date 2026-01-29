<div>
    <!-- Year Selector -->
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousYear" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <span class="text-lg font-semibold text-gray-900">{{ $schoolYear }}</span>
        <button wire:click="nextYear" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    <!-- Heat Map Grid -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="p-2 text-left text-xs font-semibold text-gray-500 uppercase"></th>
                    @foreach($quarters as $q => $label)
                    <th class="p-2 text-center text-xs font-semibold text-gray-500 uppercase w-20">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $categoryKey => $categoryLabel)
                <tr>
                    <td class="p-2 text-sm font-medium text-gray-700 whitespace-nowrap">{{ $categoryLabel }}</td>
                    @foreach($quarters as $q => $label)
                    @php
                        $cell = $heatMapData[$categoryKey][$q] ?? null;
                        $status = $cell['status'] ?? null;
                        $color = $cell['color'] ?? '#9ca3af';
                        $value = $cell['value'] ?? null;
                        $statusLabel = $cell['label'] ?? 'No Data';
                    @endphp
                    <td class="p-2">
                        <div
                            class="w-full h-14 rounded-lg flex flex-col items-center justify-center text-white text-xs font-medium cursor-pointer hover:opacity-90 transition-opacity"
                            style="background-color: {{ $color }}"
                            title="{{ $categoryLabel }} - {{ $label }}: {{ $statusLabel }}"
                        >
                            @if($value !== null)
                            <span class="text-lg font-bold">{{ is_numeric($value) ? number_format($value, 1) : $value }}</span>
                            @endif
                            <span class="text-xs opacity-80">{{ $statusLabel }}</span>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap items-center justify-center gap-4 mt-4 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" style="background-color: #22c55e"></div>
            <span class="text-gray-600">On Track</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" style="background-color: #eab308"></div>
            <span class="text-gray-600">At Risk</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" style="background-color: #ef4444"></div>
            <span class="text-gray-600">Off Track</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" style="background-color: #9ca3af"></div>
            <span class="text-gray-600">No Data</span>
        </div>
    </div>
</div>
