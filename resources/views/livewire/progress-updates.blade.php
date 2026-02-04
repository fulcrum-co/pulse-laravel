<div>
    {{-- Alignment Dashboard Cards --}}
    <div class="mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            {{-- On Track --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">On Track</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700">
                        On Track
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-green-600">{{ $this->alignmentSummary['strong_count'] }}</span>
                    <span class="text-xs text-gray-400">activities</span>
                </div>
            </div>

            {{-- Drifting --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">Drifting</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-700">
                        Drifting
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-yellow-600">{{ $this->alignmentSummary['moderate_count'] }}</span>
                    <span class="text-xs text-gray-400">activities</span>
                </div>
            </div>

            {{-- Off Track --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">Off Track</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-700">
                        Off Track
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-red-600">{{ $this->alignmentSummary['weak_count'] }}</span>
                    <span class="text-xs text-gray-400">activities</span>
                </div>
            </div>

            {{-- Avg Alignment --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3">
                @php
                    $avgScore = $this->alignmentSummary['average_alignment'];
                    $avgColor = match(true) {
                        $avgScore === null => 'gray',
                        $avgScore >= 0.85 => 'green',
                        $avgScore >= 0.65 => 'yellow',
                        default => 'red',
                    };
                @endphp
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500">Avg Alignment</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-{{ $avgColor }}-100 text-{{ $avgColor }}-700">
                        {{ $avgScore !== null ? number_format($avgScore * 100, 0) . '%' : 'N/A' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-{{ $avgColor }}-600">
                        {{ $avgScore !== null ? number_format($avgScore * 100, 0) : '—' }}
                    </span>
                    @if($avgScore !== null)
                        <span class="text-sm text-gray-400">%</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- AI Summary Card --}}
    @if($latestSummary)
        <div class="mb-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-3">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4 text-blue-600" />
                    <h4 class="text-sm font-medium text-blue-900">AI Progress Summary</h4>
                    <span class="text-[10px] text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded-full">
                        {{ $latestSummary->period_label }}
                    </span>
                </div>
                <button wire:click="generateSummary" wire:loading.attr="disabled"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    <span wire:loading.remove wire:target="generateSummary">Refresh</span>
                    <span wire:loading wire:target="generateSummary">Generating...</span>
                </button>
            </div>

            <p class="text-xs text-gray-700 mb-3">{{ $latestSummary->summary }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @if(!empty($latestSummary->highlights))
                    <div>
                        <h5 class="text-[10px] font-medium text-green-700 mb-1.5 flex items-center gap-1">
                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                            Highlights
                        </h5>
                        <ul class="space-y-0.5">
                            @foreach($latestSummary->highlights as $highlight)
                                <li class="text-[10px] text-gray-600">• {{ $highlight }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($latestSummary->concerns))
                    <div>
                        <h5 class="text-[10px] font-medium text-yellow-700 mb-1.5 flex items-center gap-1">
                            <x-icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                            Concerns
                        </h5>
                        <ul class="space-y-0.5">
                            @foreach($latestSummary->concerns as $concern)
                                <li class="text-[10px] text-gray-600">• {{ $concern }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($latestSummary->recommendations))
                    <div>
                        <h5 class="text-[10px] font-medium text-blue-700 mb-1.5 flex items-center gap-1">
                            <x-icon name="light-bulb" class="w-3.5 h-3.5" />
                            Recommendations
                        </h5>
                        <ul class="space-y-0.5">
                            @foreach($latestSummary->recommendations as $rec)
                                <li class="text-[10px] text-gray-600">• {{ $rec }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="mb-4 bg-gray-50 rounded-lg border border-gray-200 p-3 text-center">
            <x-icon name="sparkles" class="w-6 h-6 text-gray-400 mx-auto mb-1.5" />
            <p class="text-xs text-gray-600 mb-2">No AI summary generated yet</p>
            <button wire:click="generateSummary" wire:loading.attr="disabled"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white bg-blue-500 rounded hover:bg-blue-600">
                <span wire:loading.remove wire:target="generateSummary">Generate Summary</span>
                <span wire:loading wire:target="generateSummary">
                    <x-icon name="arrow-path" class="w-3.5 h-3.5 animate-spin mr-1" />
                    Generating...
                </span>
            </button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-900">Progress Updates</h3>
        <button wire:click="showForm"
            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-3.5 h-3.5 mr-1" />
            Add Update
        </button>
    </div>

    {{-- Add Update Form --}}
    @if($showAddForm)
        <div class="mb-4 bg-white rounded-lg border border-gray-200 p-3">
            <h4 class="text-xs font-medium text-gray-900 mb-2">New Progress Update</h4>
            <div class="space-y-2">
                <div>
                    <textarea wire:model="newUpdateContent" rows="2"
                        class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500"
                        placeholder="What progress has been made?"></textarea>
                    @error('newUpdateContent') <p class="text-red-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div class="w-48">
                    <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Link to Focus Area (optional)</label>
                    <select wire:model="selectedGoalId"
                        class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500">
                        <option value="">No specific area</option>
                        @foreach($goals as $goal)
                            <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="addUpdate"
                        class="px-2.5 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                        Post Update
                    </button>
                    <button wire:click="cancelForm"
                        class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Updates Feed --}}
    @if($updates->isEmpty())
        <div class="text-center py-8 bg-white rounded-lg border border-gray-200">
            <x-icon name="chat-bubble-left-right" class="w-8 h-8 text-gray-300 mx-auto mb-2" />
            <p class="text-xs text-gray-500">No updates yet.</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Post your first progress update to get started.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($updates as $update)
                <div class="bg-white rounded-lg border border-gray-200 p-3">
                    <div class="flex items-start gap-2.5">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            @if($update->update_type === 'ai_generated')
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-blue-600" />
                                </div>
                            @elseif($update->update_type === 'system')
                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                    <x-icon name="cog-6-tooth" class="w-3.5 h-3.5 text-gray-600" />
                                </div>
                            @elseif($update->creator && $update->creator->avatar_url)
                                <img src="{{ $update->creator->avatar_url }}" alt=""
                                    class="w-6 h-6 rounded-full object-cover">
                            @else
                                <div class="w-6 h-6 rounded-full bg-pulse-orange-100 flex items-center justify-center text-[10px] font-medium text-pulse-orange-600">
                                    {{ substr($update->creator->first_name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-medium text-xs text-gray-900">
                                    @if($update->update_type === 'ai_generated')
                                        AI Assistant
                                    @elseif($update->update_type === 'system')
                                        System
                                    @else
                                        {{ $update->creator->full_name ?? 'Unknown' }}
                                    @endif
                                </span>
                                <span class="text-[10px] text-gray-400">
                                    {{ $update->created_at->diffForHumans() }}
                                </span>
                                @if($update->update_type !== 'manual')
                                    <span class="px-1 py-0.5 text-[10px] font-medium rounded
                                        {{ $update->update_type === 'ai_generated' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $update->update_type === 'ai_generated' ? 'AI' : 'Auto' }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-700 whitespace-pre-wrap">{{ $update->content }}</p>

                            @if($update->context_label)
                                <div class="mt-1.5 text-[10px] text-gray-500 flex items-center gap-1">
                                    <x-icon name="link" class="w-3 h-3" />
                                    {{ $update->context_label }}
                                </div>
                            @endif

                            @if($update->value_change)
                                <div class="mt-1.5 inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded-full">
                                    <x-icon name="arrow-trending-up" class="w-3 h-3 mr-0.5" />
                                    +{{ number_format($update->value_change, 1) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($updates->hasPages())
            <div class="mt-3">
                {{ $updates->links() }}
            </div>
        @endif
    @endif
</div>
