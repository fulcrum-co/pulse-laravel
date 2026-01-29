<div class="overflow-x-auto">
    <div class="min-w-[800px]">
        {{-- Timeline Header --}}
        <div class="flex border-b border-gray-200 bg-gray-50">
            <div class="w-80 flex-shrink-0 px-4 py-3 font-medium text-sm text-gray-700 border-r border-gray-200">
                <div class="flex">
                    <span class="w-40">Name</span>
                    <span class="w-20 text-center">Start Date</span>
                    <span class="w-20 text-center">Due Date</span>
                </div>
            </div>
            <div class="flex-1 flex">
                @foreach($months as $month)
                    <div class="flex-1 px-2 py-3 text-center text-sm font-medium text-gray-600 border-r border-gray-100 last:border-r-0">
                        {{ $month['short'] }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Timeline Rows --}}
        @foreach($items as $item)
            @php
                $barStyle = $this->getBarStyle($item['start_date'], $item['end_date']);
                $statusColors = [
                    'on_track' => 'bg-green-400',
                    'at_risk' => 'bg-yellow-400',
                    'off_track' => 'bg-red-400',
                    'not_started' => 'bg-gray-300',
                ];
                $icons = [
                    'focus_area' => 'globe',
                    'objective' => 'target',
                    'activity' => 'check-circle',
                ];
                $paddings = [0 => 'pl-4', 1 => 'pl-8', 2 => 'pl-12'];
            @endphp

            <div class="flex border-b border-gray-100 hover:bg-gray-50 {{ $item['level'] > 0 ? 'text-sm' : '' }}">
                {{-- Item Info --}}
                <div class="w-80 flex-shrink-0 border-r border-gray-200 {{ $paddings[$item['level']] ?? 'pl-4' }} py-2 flex items-center gap-2">
                    @if($item['type'] === 'focus_area')
                        <button wire:click="toggleFocusArea({{ explode('_', $item['id'])[1] }})" class="text-gray-400 hover:text-gray-600">
                            <x-icon name="{{ ($expandedFocusAreas[explode('_', $item['id'])[1]] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-4 h-4" />
                        </button>
                    @elseif($item['type'] === 'objective')
                        <button wire:click="toggleObjective({{ explode('_', $item['id'])[1] }})" class="text-gray-400 hover:text-gray-600">
                            <x-icon name="{{ ($expandedObjectives[explode('_', $item['id'])[1]] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-4 h-4" />
                        </button>
                    @else
                        <span class="w-4"></span>
                    @endif

                    <x-icon name="{{ $icons[$item['type']] ?? 'document' }}" class="w-4 h-4 text-gray-400" />

                    <div class="flex-1 min-w-0">
                        <span class="truncate block {{ $item['level'] === 0 ? 'font-medium text-gray-900' : 'text-gray-700' }}">
                            {{ $item['title'] }}
                        </span>
                    </div>

                    <span class="w-20 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($item['start_date'])->format('n/j/y') }}</span>
                    <span class="w-20 text-center text-xs text-gray-500">{{ \Carbon\Carbon::parse($item['end_date'])->format('n/j/y') }}</span>
                </div>

                {{-- Gantt Bar --}}
                <div class="flex-1 relative py-2">
                    <div class="absolute inset-y-0 flex items-center" style="left: {{ $barStyle['left'] }}; width: {{ $barStyle['width'] }};">
                        <div class="h-3 rounded-full {{ $statusColors[$item['status']] ?? 'bg-gray-300' }} w-full"></div>
                    </div>
                </div>
            </div>
        @endforeach

        @if(empty($items))
            <div class="text-center py-12 text-gray-500">
                <p>No items to display in timeline.</p>
                <p class="text-sm">Add focus areas, objectives, and activities in the Planner view.</p>
            </div>
        @endif
    </div>
</div>
