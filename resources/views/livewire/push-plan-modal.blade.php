<div>
    @if($show)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">@term('push_label') @term('plan_singular') @term('to_organization_label')</h3>

                <p class="text-sm text-gray-600 mb-4">
                    @term('push_plan_help_label')
                </p>

                @if($downstreamOrgs->count() > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('select_organization_label')</label>
                        <select wire:model="selectedOrgId" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">@term('choose_organization_placeholder')</option>
                            @foreach($downstreamOrgs as $org)
                                <option value="{{ $org->id }}">{{ $org->org_name }} ({{ app(\App\Services\TerminologyService::class)->get('org_type_'.$org->org_type.'_label') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="includeSurveys" class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700">@term('include_assigned_surveys_label')</span>
                        </label>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <x-icon name="office-building" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p>@term('no_downstream_organizations_label')</p>
                        <p class="text-sm">@term('downstream_organizations_help_label')</p>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <button wire:click="close" class="px-4 py-2 text-gray-700 hover:text-gray-900">@term('cancel_label')</button>
                    @if($downstreamOrgs->count() > 0)
                        <button wire:click="push"
                            class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 disabled:opacity-50"
                            {{ !$selectedOrgId ? 'disabled' : '' }}>
                            @term('push_label') @term('plan_singular')
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
