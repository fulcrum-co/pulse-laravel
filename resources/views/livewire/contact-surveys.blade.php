<div>
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
        @foreach(['all' => $terminology->get('all_label'), 'completed' => $terminology->get('completed_label'), 'in_progress' => $terminology->get('in_progress_label'), 'abandoned' => $terminology->get('abandoned_label')] as $status => $label)
        <button
            wire:click="setFilterStatus('{{ $status }}')"
            class="px-3 py-1 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $filterStatus === $status ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
        >
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Survey Attempts List -->
    <div class="space-y-3">
        @forelse($attempts as $attempt)
        <div class="border border-gray-200 rounded-lg overflow-hidden {{ $expandedAttemptId === $attempt->id ? 'ring-2 ring-pulse-orange-200' : '' }}">
            <!-- Header Row (Clickable) -->
            <button
                wire:click="toggleExpand({{ $attempt->id }})"
                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        {{ $attempt->status === 'completed' ? 'bg-green-100' : ($attempt->status === 'in_progress' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                        @if($attempt->status === 'completed')
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        @elseif($attempt->status === 'in_progress')
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $attempt->survey->title ?? (app(\App\Services\TerminologyService::class)->get('unknown_label') . ' ' . app(\App\Services\TerminologyService::class)->get('survey_singular')) }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $attempt->completed_at?->format('M d, Y h:i A') ?? ($attempt->started_at?->format('M d, Y h:i A') ?? $attempt->created_at->format('M d, Y h:i A')) }}
                            @if($attempt->response_channel)
                            <span class="text-gray-400">@term('via_label') {{ $terminology->get('response_channel_'.$attempt->response_channel.'_label') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($attempt->overall_score !== null)
                    <span class="text-lg font-semibold {{ $attempt->risk_level === 'high' ? 'text-red-600' : ($attempt->risk_level === 'medium' ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ number_format($attempt->overall_score, 1) }}
                    </span>
                    @endif
                    <span class="px-2 py-0.5 text-xs rounded-full
                        {{ $attempt->status === 'completed' ? 'bg-green-100 text-green-700' : ($attempt->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $terminology->get(($attempt->status ?? 'unknown').'_label') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform {{ $expandedAttemptId === $attempt->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </button>

            <!-- Expanded Content -->
            @if($expandedAttemptId === $attempt->id)
            <div class="border-t border-gray-200 p-4 bg-white">
                <!-- Actions -->
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-gray-700">@term('responses_label')</h4>
                    @if($editingAttemptId !== $attempt->id)
                    <button
                        wire:click="startEdit({{ $attempt->id }})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        @term('edit_scores_label')
                    </button>
                    @else
                    <div class="flex gap-2">
                        <button
                            wire:click="saveChanges"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @term('save_changes_label')
                        </button>
                        <button
                            wire:click="cancelEdit"
                            class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            @term('cancel_action')
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Questions and Responses -->
                @if($attempt->survey && $attempt->survey->questions)
                <div class="space-y-4">
                    @foreach($attempt->survey->questions as $index => $question)
                    @php
                        $questionId = $question['id'] ?? "q{$index}";
                        $response = $editingAttemptId === $attempt->id
                            ? ($editingResponses[$questionId] ?? null)
                            : ($attempt->responses[$questionId] ?? null);
                    @endphp
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    {{ $index + 1 }}. {{ $question['text'] ?? $question['question'] ?? app(\App\Services\TerminologyService::class)->get('question_singular') }}
                                </p>
                                @if($editingAttemptId === $attempt->id)
                                <!-- Edit Mode -->
                                @if(($question['type'] ?? 'text') === 'scale')
                                <div class="flex items-center gap-2 mt-2">
                                    @php
                                        $min = $question['scale_min'] ?? 1;
                                        $max = $question['scale_max'] ?? 10;
                                    @endphp
                                    <input
                                        type="range"
                                        min="{{ $min }}"
                                        max="{{ $max }}"
                                        value="{{ $response ?? $min }}"
                                        wire:change="updateResponse('{{ $questionId }}', $event.target.value)"
                                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                    >
                                    <input
                                        type="number"
                                        min="{{ $min }}"
                                        max="{{ $max }}"
                                        value="{{ $response ?? '' }}"
                                        wire:change="updateResponse('{{ $questionId }}', $event.target.value)"
                                        class="w-16 px-2 py-1 text-center border border-gray-300 rounded text-sm"
                                    >
                                </div>
                                @elseif(($question['type'] ?? 'text') === 'choice' && isset($question['options']))
                                <select
                                    wire:change="updateResponse('{{ $questionId }}', $event.target.value)"
                                    class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                >
                                    <option value="">@term('select_placeholder_label')</option>
                                    @foreach($question['options'] as $option)
                                    <option value="{{ is_array($option) ? ($option['value'] ?? $option['label']) : $option }}" {{ $response == (is_array($option) ? ($option['value'] ?? $option['label']) : $option) ? 'selected' : '' }}>
                                        {{ is_array($option) ? ($option['label'] ?? $option['value']) : $option }}
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <textarea
                                    wire:change="updateResponse('{{ $questionId }}', $event.target.value)"
                                    rows="2"
                                    class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none"
                                >{{ $response ?? '' }}</textarea>
                                @endif
                                @else
                                <!-- View Mode -->
                                <p class="text-sm text-gray-600">
                                    @if($response !== null)
                                        @if(($question['type'] ?? 'text') === 'scale')
                                        <span class="inline-flex items-center gap-2">
                                            <span class="font-medium text-lg">{{ $response }}</span>
                                            <span class="text-gray-400">/ {{ $question['scale_max'] ?? 10 }}</span>
                                        </span>
                                        @else
                                        {{ is_array($response) ? implode(', ', $response) : $response }}
                                        @endif
                                    @else
                                    <span class="text-gray-400 italic">@term('no_response_label')</span>
                                    @endif
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-gray-500">
                    <p class="text-sm">@term('no_question_data_label')</p>
                </div>
                @endif

                <!-- AI Analysis (if available) -->
                @if($attempt->ai_analysis)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <h5 class="text-sm font-medium text-blue-900 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        @term('ai_analysis_label')
                    </h5>
                    @if(isset($attempt->ai_analysis['summary']))
                    <p class="text-sm text-blue-800">{{ $attempt->ai_analysis['summary'] }}</p>
                    @endif
                    @if(isset($attempt->ai_analysis['recommendations']))
                    <div class="mt-2">
                        <p class="text-xs font-medium text-blue-700 mb-1">@term('recommendations_label'):</p>
                        <ul class="text-sm text-blue-800 list-disc list-inside">
                            @foreach($attempt->ai_analysis['recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Risk Level Badge -->
                @if($attempt->risk_level)
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-sm text-gray-500">@term('risk_level_label'):</span>
                    <span class="px-2 py-1 text-sm font-medium rounded-full
                        {{ $attempt->risk_level === 'high' ? 'bg-red-100 text-red-700' : ($attempt->risk_level === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                        {{ $terminology->get('risk_'.$attempt->risk_level.'_label') }}
                    </span>
                </div>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p>@term('no_surveys_completed_label')</p>
            <a href="{{ route('surveys.index') }}" class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                @term('send_action') @term('survey_singular')
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($attempts->hasPages())
    <div class="mt-4">
        {{ $attempts->links() }}
    </div>
    @endif
</div>
