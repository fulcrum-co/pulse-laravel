<div class="overflow-x-auto">
    {{-- Legend --}}
    <div class="flex items-center gap-3 mb-3 text-[10px] text-gray-500">
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-green-500"></span> Focus Area
        </span>
        <span class="text-gray-300">→</span>
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-purple-500"></span> Key Activity
        </span>
        <span class="mx-2 text-gray-300">|</span>
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-green-400"></span> On Track
        </span>
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-yellow-400"></span> At Risk
        </span>
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-red-400"></span> Off Track
        </span>
        <span class="flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded bg-gray-300"></span> Not Started
        </span>
    </div>

    <div class="min-w-[700px]">
        {{-- Timeline Header --}}
        <div class="flex border-b border-gray-200 bg-gray-50">
            <div class="w-72 flex-shrink-0 px-3 py-2 font-medium text-[10px] text-gray-600 border-r border-gray-200">
                <div class="flex">
                    <span class="flex-1">Name</span>
                    <span class="w-14 text-center">Start</span>
                    <span class="w-14 text-center">Due</span>
                </div>
            </div>
            <div class="flex-1 flex">
                @foreach($months as $month)
                    <div class="flex-1 px-1 py-2 text-center text-[10px] font-medium text-gray-500 border-r border-gray-100 last:border-r-0">
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
                $paddings = [0 => 'pl-3', 1 => 'pl-8'];
            @endphp

            <div class="flex border-b border-gray-100 hover:bg-gray-50/50 text-xs">
                {{-- Item Info --}}
                <div class="w-72 flex-shrink-0 border-r border-gray-200 {{ $paddings[$item['level']] ?? 'pl-3' }} py-1.5 flex items-center gap-1.5">
                    @if($item['type'] === 'goal')
                        <button wire:click="toggleGoal({{ explode('_', $item['id'])[1] }})" class="text-green-400 hover:text-green-600">
                            <x-icon name="{{ ($expandedGoals[explode('_', $item['id'])[1]] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-3.5 h-3.5" />
                        </button>
                    @else
                        <span class="w-3.5"></span>
                    @endif

                    <div class="w-2 h-2 rounded {{ $item['type'] === 'goal' ? 'bg-green-500' : 'bg-purple-500' }} flex-shrink-0"></div>

                    <div class="flex-1 min-w-0">
                        <span class="truncate block text-xs {{ $item['level'] === 0 ? 'font-medium text-gray-900' : 'text-gray-600' }}">
                            {{ $item['title'] }}
                        </span>
                    </div>

                    <span class="w-14 text-center text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($item['start_date'])->format('n/j') }}</span>
                    <span class="w-14 text-center text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($item['end_date'])->format('n/j') }}</span>
                </div>

                {{-- Gantt Bar --}}
                <div class="flex-1 relative py-1.5">
                    <div class="absolute inset-y-0 flex items-center" style="left: {{ $barStyle['left'] }}; width: {{ $barStyle['width'] }};">
                        <div class="relative h-3 rounded-full {{ $statusColors[$item['status']] ?? 'bg-gray-300' }} w-full overflow-hidden">
                            @if(isset($item['progress']) && $item['progress'] > 0)
                                <div class="absolute inset-y-0 left-0 bg-black/10 rounded-full"
                                    style="width: {{ min($item['progress'], 100) }}%"></div>
                            @endif
                            @if(isset($item['progress']))
                                <span class="absolute inset-0 flex items-center justify-center text-[10px] font-medium text-white drop-shadow-sm">
                                    {{ number_format($item['progress'], 0) }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if(empty($items))
            <div class="text-center py-8 text-gray-500">
                <x-icon name="calendar" class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                <p class="text-xs">No items to display</p>
                <p class="text-[10px] mt-0.5 text-gray-400">Add focus areas and activities in the Focus Areas view</p>
            </div>
        @endif
    </div>
</div>
