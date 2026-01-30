<x-layouts.dashboard title="Reports">
    <x-slot name="actions">
        <a href="{{ route('reports.create') }}">
            <x-button variant="primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Create Report
            </x-button>
        </a>
    </x-slot>

    @if($reports->isEmpty())
        <x-card>
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gradient-to-br from-pulse-orange-100 to-pulse-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <x-icon name="chart-pie" class="w-10 h-10 text-pulse-orange-500" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Create your first report</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Build beautiful, data-driven reports with our drag-and-drop editor.
                    Choose from templates or start from scratch.
                </p>
                <a href="{{ route('reports.create') }}">
                    <x-button variant="primary" size="lg">
                        <x-icon name="plus" class="w-5 h-5 mr-2" />
                        Create Report
                    </x-button>
                </a>
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reports as $report)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow group">
                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-50 relative">
                        @if($report->thumbnail_path)
                            <img src="{{ $report->thumbnail_path }}" alt="{{ $report->report_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-icon name="document-report" class="w-16 h-16 text-gray-300" />
                            </div>
                        @endif

                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <a href="{{ route('reports.edit', $report) }}" class="bg-white text-gray-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                Edit
                            </a>
                            @if($report->isPublished())
                                <a href="{{ $report->getPublicUrl() }}" target="_blank" class="bg-pulse-orange-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                                    View
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $report->report_name }}</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Updated {{ $report->updated_at->diffForHumans() }}
                                </p>
                            </div>

                            <!-- Status badge -->
                            @if($report->isPublished())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('reports.edit', $report) }}" class="flex-1 text-center py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('reports.duplicate', $report) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full text-center py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                                    Duplicate
                                </button>
                            </form>
                            <div x-data class="inline-block">
                                <button
                                    type="button"
                                    @click="if(confirm('Are you sure you want to delete this report?')) { $refs.deleteForm.submit() }"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                                <form x-ref="deleteForm" action="{{ route('reports.destroy', $report) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @endif
</x-layouts.dashboard>
