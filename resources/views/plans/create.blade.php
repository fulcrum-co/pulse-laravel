<x-layouts.dashboard title="@term('create_action') @term('plan_singular')">
    <div class="max-w-2xl">
        <x-card>
            <form action="{{ route('plans.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
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
                        placeholder="@term('plan_description_placeholder')">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-700 mb-1">@term('plan_type_label')</label>
                    <select name="plan_type" id="plan_type" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        <option value="organizational" {{ ($type ?? old('plan_type')) === 'organizational' ? 'selected' : '' }}>@term('plan_type_organizational_label')</option>
                        <option value="instructor" {{ ($type ?? old('plan_type')) === 'instructor' ? 'selected' : '' }}>@term('plan_type_team_label')</option>
                        <option value="participant" {{ ($type ?? old('plan_type')) === 'participant' ? 'selected' : '' }}>@term('plan_type_learner_label')</option>
                        <option value="department" {{ ($type ?? old('plan_type')) === 'department' ? 'selected' : '' }}>@term('plan_type_department_label')</option>
                        <option value="level" {{ ($type ?? old('plan_type')) === 'level' ? 'selected' : '' }}>@term('plan_type_level_label')</option>
                    </select>
                    @error('plan_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">@term('start_date_label')</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">@term('end_date_label')</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('plans.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">
                        @term('cancel_action')
                    </a>
                    <button type="submit" class="px-6 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                        @term('create_action') @term('plan_singular')
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.dashboard>
