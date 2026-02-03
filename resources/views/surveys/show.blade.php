<x-layouts.dashboard title="{{ $survey->title }}">
    <x-slot name="actions">
        <div class="flex items-center gap-3">
            <a href="{{ route('surveys.edit', $survey) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <x-icon name="pencil" class="w-4 h-4 mr-2" />
                @term('edit_action')
            </a>
            @if($survey->status === 'active')
                <a href="{{ route('surveys.deliver.form', $survey) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    <x-icon name="paper-airplane" class="w-4 h-4 mr-2" />
                    @term('send_action') @term('survey_singular')
                </a>
            @endif
            @if($survey->status === 'draft')
                <form action="{{ route('surveys.toggle', $survey) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <x-icon name="play" class="w-4 h-4 mr-2" />
                        @term('activate_action')
                    </button>
                </form>
            @else
                <form action="{{ route('surveys.toggle', $survey) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <x-icon name="pause" class="w-4 h-4 mr-2" />
                        {{ $survey->status === 'active' ? app(\App\Services\TerminologyService::class)->get('pause_action') : app(\App\Services\TerminologyService::class)->get('resume_action') }}
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('surveys.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            @term('back_to_label') @term('survey_plural')
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Survey Info Card -->
            <x-card>
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $survey->title }}</h1>
                        @if($survey->description)
                            <p class="text-gray-600 mt-2">{{ $survey->description }}</p>
                        @endif
                    </div>
                    <x-badge :color="match($survey->status) {
                        'active' => 'green',
                        'draft' => 'gray',
                        'paused' => 'yellow',
                        'completed' => 'blue',
                        default => 'gray',
                    }">{{ ucfirst($survey->status) }}</x-badge>
                </div>

                <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-500">
                    <div class="flex items-center gap-1">
                        <x-icon name="tag" class="w-4 h-4" />
                        {{ ucfirst($survey->survey_type) }}
                    </div>
                    <div class="flex items-center gap-1">
                        <x-icon name="question-mark-circle" class="w-4 h-4" />
                        {{ count($survey->questions ?? []) }} questions
                    </div>
                    <div class="flex items-center gap-1">
                        <x-icon name="clock" class="w-4 h-4" />
                        ~{{ $survey->estimated_duration_minutes ?? 5 }} min
                    </div>
                    <div class="flex items-center gap-1">
                        <x-icon name="calendar" class="w-4 h-4" />
                        Created {{ $survey->created_at->format('M d, Y') }}
                    </div>
                </div>
            </x-card>

            <!-- Questions Card -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('questions_label')</h2>

                <div class="space-y-4">
                    @foreach($survey->questions ?? [] as $index => $question)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-white rounded-lg flex items-center justify-center text-sm font-medium text-gray-500 border border-gray-200">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-gray-900 font-medium">{{ $question['question'] }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <x-badge color="gray">{{ ucfirst(str_replace('_', ' ', $question['type'] ?? 'scale')) }}</x-badge>
                                        @if($question['required'] ?? true)
                                            <x-badge color="red">@term('required_label')</x-badge>
                                        @endif
                                    </div>

                                    @if(($question['type'] ?? 'scale') === 'scale' && isset($question['options']))
                                        <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                                            <span>1 = {{ $question['options']['1'] ?? app(\App\Services\TerminologyService::class)->get('scale_low_label') }}</span>
                                            <span>5 = {{ $question['options']['5'] ?? app(\App\Services\TerminologyService::class)->get('scale_high_label') }}</span>
                                        </div>
                                    @elseif(($question['type'] ?? 'scale') === 'multiple_choice' && isset($question['options']))
                                        <div class="mt-3 space-y-1">
                                            @foreach($question['options'] as $option)
                                                <div class="text-sm text-gray-600 flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                                    {{ $option }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Recent Responses Card -->
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">@term('recent_label') @term('responses_label')</h2>
                    <span class="text-sm text-gray-500">{{ $survey->attempts()->completed()->count() }} @term('total_label')</span>
                </div>

                @if($survey->attempts->count() > 0)
                    <div class="space-y-3">
                        @foreach($survey->attempts->take(5) as $attempt)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    @if($survey->is_anonymous)
                                        <span class="text-gray-600">@term('anonymous_label') @term('responses_label')</span>
                                    @else
                                        <span class="font-medium text-gray-900">{{ $attempt->learner?->name ?? 'Unknown' }}</span>
                                    @endif
                                    <div class="text-sm text-gray-500">{{ $attempt->completed_at?->diffForHumans() ?? app(\App\Services\TerminologyService::class)->get('in_progress_label') }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($attempt->risk_level)
                                        <x-badge :color="match($attempt->risk_level) {
                                            'high' => 'red',
                                            'medium' => 'yellow',
                                            'low' => 'green',
                                            default => 'gray',
                                        }">{{ ucfirst($attempt->risk_level) }} Risk</x-badge>
                                    @endif
                                    @if($attempt->overall_score)
                                        <span class="text-sm font-medium text-gray-600">{{ round($attempt->overall_score, 1) }}/5</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="clipboard-document" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500">@term('no_label') @term('responses_label') yet</p>
                        <p class="text-sm text-gray-400">@term('responses_label') will appear here once @term('learner_plural') @term('complete_action') the @term('survey_singular')</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stats Card -->
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">@term('statistics_label')</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('total_label') @term('responses_label')</span>
                        <span class="text-2xl font-bold text-gray-900">{{ $survey->attempts()->completed()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('in_progress_label')</span>
                        <span class="text-lg font-medium text-gray-700">{{ $survey->attempts()->where('status', 'in_progress')->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('completion_rate_label')</span>
                        @php
                            $total = $survey->attempts()->count();
                            $completed = $survey->attempts()->completed()->count();
                            $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <span class="text-lg font-medium text-gray-700">{{ $rate }}%</span>
                    </div>
                </div>
            </x-card>

            <!-- Delivery Channels Card -->
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">@term('delivery_label') @term('channel_plural')</h3>

                <div class="space-y-2">
                    @foreach($survey->delivery_channels ?? ['web'] as $channel)
                        <div class="flex items-center gap-2 text-gray-600">
                            <x-icon name="{{ match($channel) {
                                'web' => 'globe-alt',
                                'sms' => 'chat-bubble-left',
                                'voice_call' => 'phone',
                                'whatsapp' => 'chat-bubble-oval-left',
                                default => 'device-phone-mobile',
                            } }}" class="w-5 h-5 text-gray-400" />
                            <span>{{ match($channel) {
                                'web' => app(\App\Services\TerminologyService::class)->get('web_link_label'),
                                'sms' => app(\App\Services\TerminologyService::class)->get('sms_label'),
                                'voice_call' => app(\App\Services\TerminologyService::class)->get('voice_call_label'),
                                'whatsapp' => app(\App\Services\TerminologyService::class)->get('whatsapp_label'),
                                default => ucfirst($channel),
                            } }}</span>
                        </div>
                    @endforeach
                </div>

                @if($survey->status === 'active')
                    <button
                        onclick="document.getElementById('deliveryModal').classList.remove('hidden')"
                        class="mt-4 w-full px-4 py-2 text-sm font-medium text-pulse-orange-600 bg-pulse-orange-50 rounded-lg hover:bg-pulse-orange-100"
                    >
                        <x-icon name="paper-airplane" class="w-4 h-4 inline mr-1" />
                        @term('send_action') @term('survey_singular')
                    </button>
                @endif
            </x-card>

            <!-- Settings Card -->
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">@term('settings_label')</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('anonymous_label')</span>
                        <span class="font-medium {{ $survey->is_anonymous ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $survey->is_anonymous ? app(\App\Services\TerminologyService::class)->get('yes_label') : app(\App\Services\TerminologyService::class)->get('no_label') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('ai_follow_up_label')</span>
                        <span class="font-medium {{ $survey->ai_follow_up_enabled ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $survey->ai_follow_up_enabled ? app(\App\Services\TerminologyService::class)->get('enabled_label') : app(\App\Services\TerminologyService::class)->get('disabled_label') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">@term('voice_responses_label')</span>
                        <span class="font-medium {{ $survey->allow_voice_responses ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $survey->allow_voice_responses ? app(\App\Services\TerminologyService::class)->get('allowed_label') : app(\App\Services\TerminologyService::class)->get('disabled_label') }}
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Actions Card -->
            <x-card class="bg-gray-50">
                <div class="space-y-2">
                    @php
                        $user = auth()->user();
                        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
                        $hasAssignedOrgs = $user->organizations()->count() > 0;
                        $canPush = ($user->isAdmin() && $hasDownstream) || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
                    @endphp
                    @if($canPush)
                        <button
                            type="button"
                            onclick="Livewire.dispatch('openPushSurvey', [{{ $survey->id }}])"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 flex items-center justify-center gap-2"
                        >
                            <x-icon name="arrow-up-on-square" class="w-4 h-4" />
                            @term('push_label') to @term('organization_plural')
                        </button>
                    @endif
                    <form action="{{ route('surveys.duplicate', $survey) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2">
                            <x-icon name="document-duplicate" class="w-4 h-4" />
                            @term('duplicate_action') @term('survey_singular')
                        </button>
                    </form>
                    <form action="{{ route('surveys.destroy', $survey) }}" method="POST" onsubmit="return confirm('{{ app(\App\Services\TerminologyService::class)->get('confirm_delete_survey_label') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 flex items-center justify-center gap-2">
                            <x-icon name="trash" class="w-4 h-4" />
                            @term('delete_action') @term('survey_singular')
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Delivery Modal -->
    <div id="deliveryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('deliveryModal').classList.add('hidden')"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('send_action') @term('survey_singular')</h3>

                <form action="{{ route('surveys.deliver', $survey) }}" method="POST" id="deliveryForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">@term('delivery_label') @term('channel_singular')</label>
                            <select name="channel" class="mt-1 block w-full rounded-lg border-gray-300">
                                @foreach($survey->delivery_channels ?? ['web'] as $channel)
                                    <option value="{{ $channel }}">{{ ucfirst(str_replace('_', ' ', $channel)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">@term('phone_number_label') (for SMS/Voice)</label>
                            <input
                                type="tel"
                                name="phone_number"
                                class="mt-1 block w-full rounded-lg border-gray-300"
                                placeholder="+1 (555) 123-4567"
                            />
                        </div>

                        <div class="text-sm text-gray-500">
                            This will @term('send_action') the @term('survey_singular') to the specified @term('recipient_singular') via the selected @term('channel_singular').
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            onclick="document.getElementById('deliveryModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                        >
                            @term('cancel_action')
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                        >
                            @term('send_action') @term('survey_singular')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Push Content Modal -->
    @livewire('push-content-modal')
</x-layouts.dashboard>
