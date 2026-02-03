<x-layouts.dashboard title="@term('edit_action') @term('plan_singular')">
    <div class="max-w-2xl">
        <x-card>
            <form action="{{ route('plans.update', $plan) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $plan->title) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="@term('plan_title_placeholder')">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">@term('description_label')</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="@term('plan_description_placeholder')">{{ old('description', $plan->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">@term('status_label')</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        <option value="draft" {{ old('status', $plan->status) === 'draft' ? 'selected' : '' }}>@term('draft_label')</option>
                        <option value="active" {{ old('status', $plan->status) === 'active' ? 'selected' : '' }}>@term('active_label')</option>
                        <option value="completed" {{ old('status', $plan->status) === 'completed' ? 'selected' : '' }}>@term('completed_label')</option>
                        <option value="archived" {{ old('status', $plan->status) === 'archived' ? 'selected' : '' }}>@term('archived_label')</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">@term('start_date_label')</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $plan->start_date->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">@term('end_date_label')</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $plan->end_date->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if($plan->source_org_id)
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="consultant_visible" value="1" {{ old('consultant_visible', $plan->consultant_visible) ? 'checked' : '' }}
                            class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500">
                        <span class="text-sm text-gray-700">@term('plan_allow_upstream_consultant_label')</span>
                    </label>
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('plans.show', $plan) }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">
                        @term('cancel_action')
                    </a>
                    <button type="submit" class="px-6 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                        @term('save_changes_label')
                    </button>
                </div>
            </form>
        </x-card>

        {{-- Danger Zone --}}
        <x-card class="mt-6 border-red-200">
            <h3 class="text-lg font-medium text-red-600 mb-4">@term('danger_zone_label')</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">@term('delete_plan_label')</p>
                    <p class="text-sm text-gray-500">@term('delete_plan_warning_label')</p>
                </div>
                <form action="{{ route('plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('{{ app(\\App\\Services\\TerminologyService::class)->get('delete_plan_confirm_label') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                        @term('delete_action') @term('plan_singular')
                    </button>
                </form>
            </div>
        </x-card>
    </div>
</x-layouts.dashboard>
