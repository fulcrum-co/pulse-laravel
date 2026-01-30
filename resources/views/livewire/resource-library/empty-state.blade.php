<x-card>
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <x-icon name="folder-open" class="w-8 h-8 text-pulse-orange-500" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">No results found</h3>
        <p class="text-gray-500 mb-4 max-w-sm mx-auto text-sm">
            {{ $message ?? 'Try adjusting your search or filters.' }}
        </p>
    </div>
</x-card>
