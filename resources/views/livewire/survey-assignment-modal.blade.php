<div>
    @if($show)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">@term('assign_action') @term('survey_plural')</h3>

                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4"
                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_action') }} {{ strtolower(app(\App\Services\TerminologyService::class)->get('survey_plural')) }}...">

                {{-- Assigned Surveys --}}
                @if($assignedSurveys->count() > 0)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">@term('assigned_label') @term('survey_plural')</h4>
                        <div class="space-y-2">
                            @foreach($assignedSurveys as $survey)
                                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded">
                                    <span class="text-sm">{{ $survey->title }}</span>
                                    <button wire:click="removeSurvey({{ $survey->id }})" class="text-gray-400 hover:text-red-500">
                                        <x-icon name="x" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Available Surveys --}}
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">@term('available_label') @term('survey_plural')</h4>
                    @if($availableSurveys->count() > 0)
                        <div class="border border-gray-200 rounded-lg divide-y max-h-48 overflow-y-auto">
                            @foreach($availableSurveys as $survey)
                                @if(!$assignedSurveys->contains('id', $survey->id))
                                    <button wire:click="assignSurvey({{ $survey->id }})"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center justify-between">
                                        <span class="text-sm">{{ $survey->title }}</span>
                                        <x-icon name="plus" class="w-4 h-4 text-pulse-orange-500" />
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">@term('no_surveys_available_label')</p>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button wire:click="close" class="px-4 py-2 text-gray-700 hover:text-gray-900">@term('close_label')</button>
                </div>
            </div>
        </div>
    @endif
</div>
