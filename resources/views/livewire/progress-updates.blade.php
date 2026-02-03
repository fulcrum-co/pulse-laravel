<div>
    {{-- AI Summary Card --}}
    @if($latestSummary)
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <x-icon name="sparkles" class="w-5 h-5 text-blue-600" />
                    <h4 class="font-medium text-blue-900">@term('ai_progress_summary_label')</h4>
                    <span class="text-xs text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">
                        {{ $latestSummary->period_label }}
                    </span>
                </div>
                <button wire:click="generateSummary" wire:loading.attr="disabled"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    <span wire:loading.remove wire:target="generateSummary">@term('refresh_label')</span>
                    <span wire:loading wire:target="generateSummary">@term('generating_label')</span>
                </button>
            </div>

            <p class="text-sm text-gray-700 mb-4">{{ $latestSummary->summary }}</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if(!empty($latestSummary->highlights))
                    <div>
                        <h5 class="text-xs font-medium text-green-700 mb-2 flex items-center gap-1">
                            <x-icon name="check-circle" class="w-4 h-4" />
                            @term('highlights_label')
                        </h5>
                        <ul class="space-y-1">
                            @foreach($latestSummary->highlights as $highlight)
                                <li class="text-xs text-gray-600">• {{ $highlight }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($latestSummary->concerns))
                    <div>
                        <h5 class="text-xs font-medium text-yellow-700 mb-2 flex items-center gap-1">
                            <x-icon name="exclamation-triangle" class="w-4 h-4" />
                            @term('concerns_label')
                        </h5>
                        <ul class="space-y-1">
                            @foreach($latestSummary->concerns as $concern)
                                <li class="text-xs text-gray-600">• {{ $concern }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($latestSummary->recommendations))
                    <div>
                        <h5 class="text-xs font-medium text-blue-700 mb-2 flex items-center gap-1">
                            <x-icon name="light-bulb" class="w-4 h-4" />
                            @term('recommendations_label')
                        </h5>
                        <ul class="space-y-1">
                            @foreach($latestSummary->recommendations as $rec)
                                <li class="text-xs text-gray-600">• {{ $rec }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="mb-6 bg-gray-50 rounded-lg border border-gray-200 p-4 text-center">
            <x-icon name="sparkles" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-600 mb-2">@term('no_ai_summary_yet_label')</p>
            <button wire:click="generateSummary" wire:loading.attr="disabled"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                <span wire:loading.remove wire:target="generateSummary">@term('generate_summary_label')</span>
                <span wire:loading wire:target="generateSummary">
                    <x-icon name="arrow-path" class="w-4 h-4 animate-spin mr-1" />
                    @term('generating_label')
                </span>
            </button>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">@term('progress_updates_label')</h3>
        <button wire:click="showForm"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            @term('add_update_label')
        </button>
    </div>

    {{-- Add Update Form --}}
    @if($showAddForm)
        <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="font-medium text-gray-900 mb-3">@term('new_progress_update_label')</h4>
            <div class="space-y-3">
                <div>
                    <textarea wire:model="newUpdateContent" rows="3"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="@term('progress_update_placeholder')"></textarea>
                    @error('newUpdateContent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="w-64">
                    <label class="block text-xs font-medium text-gray-500 mb-1">@term('link_to_goal_optional_label')</label>
                    <select wire:model="selectedGoalId"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500">
                        <option value="">@term('no_specific_goal_label')</option>
                        @foreach($goals as $goal)
                            <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="addUpdate"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                        @term('post_update_label')
                    </button>
                    <button wire:click="cancelForm"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        @term('cancel_label')
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Updates Feed --}}
    @if($updates->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <x-icon name="chat-bubble-left-right" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500">@term('no_updates_yet_label')</p>
            <p class="text-gray-400 text-sm mt-1">@term('post_first_update_help_label')</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($updates as $update)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            @if($update->update_type === 'ai_generated')
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <x-icon name="sparkles" class="w-4 h-4 text-blue-600" />
                                </div>
                            @elseif($update->update_type === 'system')
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <x-icon name="cog-6-tooth" class="w-4 h-4 text-gray-600" />
                                </div>
                            @elseif($update->creator && $update->creator->avatar_url)
                                <img src="{{ $update->creator->avatar_url }}" alt=""
                                    class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-pulse-orange-100 flex items-center justify-center text-sm font-medium text-pulse-orange-600">
                                    {{ substr($update->creator->first_name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-sm text-gray-900">
                                    @if($update->update_type === 'ai_generated')
                                        @term('ai_assistant_label')
                                    @elseif($update->update_type === 'system')
                                        @term('system_label')
                                    @else
                                        {{ $update->creator->full_name ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $update->created_at->diffForHumans() }}
                                </span>
                                @if($update->update_type !== 'manual')
                                    <span class="px-1.5 py-0.5 text-xs font-medium rounded
                                        {{ $update->update_type === 'ai_generated' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $update->update_type === 'ai_generated' ? app(\App\Services\TerminologyService::class)->get('ai_label') : app(\App\Services\TerminologyService::class)->get('auto_label') }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $update->content }}</p>

                            @if($update->context_label)
                                <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                                    <x-icon name="link" class="w-3.5 h-3.5" />
                                    {{ $update->context_label }}
                                </div>
                            @endif

                            @if($update->value_change)
                                <div class="mt-2 inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                    <x-icon name="arrow-trending-up" class="w-3.5 h-3.5 mr-1" />
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
            <div class="mt-4">
                {{ $updates->links() }}
            </div>
        @endif
    @endif
</div>
