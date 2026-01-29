<div>
    @if($show)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">Push Strategy to Organization</h3>

                <p class="text-sm text-gray-600 mb-4">
                    Select a downstream organization to push this strategy to. A copy will be created in their account.
                </p>

                @if($downstreamOrgs->count() > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Organization</label>
                        <select wire:model="selectedOrgId" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Choose an organization...</option>
                            @foreach($downstreamOrgs as $org)
                                <option value="{{ $org->id }}">{{ $org->org_name }} ({{ ucfirst($org->org_type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="includeSurveys" class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700">Include assigned surveys</span>
                        </label>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <x-icon name="office-building" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p>No downstream organizations available.</p>
                        <p class="text-sm">You can only push to organizations that are children of your organization.</p>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <button wire:click="close" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</button>
                    @if($downstreamOrgs->count() > 0)
                        <button wire:click="push"
                            class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 disabled:opacity-50"
                            {{ !$selectedOrgId ? 'disabled' : '' }}>
                            Push Strategy
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
