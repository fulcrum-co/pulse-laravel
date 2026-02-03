<div>
    @if($show)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" x-data>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="$wire.close()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">@term('push_label') {{ $contentTypeLabel }} @term('to_organizations_label')</h3>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="text-sm text-gray-600 mb-2">
                    @term('pushing_label'): <span class="font-medium text-gray-900">{{ $contentTitle }}</span>
                </p>

                <p class="text-sm text-gray-500 mb-4">
                    @term('push_content_help_prefix') {{ strtolower($contentTypeLabel) }} @term('push_content_help_suffix')
                </p>

                @if($downstreamOrgs->count() > 0)
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">@term('select_organizations_label')</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll" class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded">
                                <span class="text-gray-600">@term('select_all_label')</span>
                            </label>
                        </div>

                        <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg divide-y">
                            @foreach($downstreamOrgs as $org)
                                <label class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" wire:model.live="selectedOrgIds" value="{{ $org->id }}" class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded">
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-medium text-gray-900">{{ $org->org_name }}</span>
                                        <span class="text-xs text-gray-500 capitalize">@term('org_type_' . $org->org_type . '_label')</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if(count($selectedOrgIds) > 0)
                            <p class="mt-2 text-sm text-gray-500">
                                {{ count($selectedOrgIds) }} @term('organizations_selected_label')
                            </p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 border border-gray-200 rounded-lg bg-gray-50">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="font-medium">@term('no_downstream_organizations_label')</p>
                        <p class="text-sm mt-1">@term('downstream_organizations_help_label')</p>
                    </div>
                @endif

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button wire:click="close" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">
                        @term('cancel_label')
                    </button>
                    @if($downstreamOrgs->count() > 0)
                        <button wire:click="push"
                            class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            {{ count($selectedOrgIds) === 0 ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            @term('push_label') {{ $contentTypeLabel }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
