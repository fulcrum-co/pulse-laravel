<x-layouts.dashboard title="Surveys">
    <x-slot name="actions">
        <x-button variant="primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Create Survey
        </x-button>
    </x-slot>

    <x-card>
        @if($surveys->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Survey</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Responses</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($surveys as $survey)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $survey->title }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($survey->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <x-badge color="purple">{{ ucfirst($survey->survey_type) }}</x-badge>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColor = match($survey->status) {
                                    'active' => 'green',
                                    'draft' => 'gray',
                                    'paused' => 'yellow',
                                    'completed' => 'blue',
                                    default => 'gray',
                                };
                            @endphp
                            <x-badge :color="$statusColor">{{ ucfirst($survey->status) }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $survey->attempts()->completed()->count() }} completed
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $survey->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="Edit">
                                    <x-icon name="edit" class="w-4 h-4 text-gray-600" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($surveys->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $surveys->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-12">
            <x-icon name="clipboard-list" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No surveys yet.</p>
            <p class="text-gray-400 text-sm mt-1">Create your first survey to get started.</p>
        </div>
        @endif
    </x-card>
</x-layouts.dashboard>
