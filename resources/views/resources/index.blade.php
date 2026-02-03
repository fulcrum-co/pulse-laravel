<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('resource_plural') }}">
    <x-slot name="actions">
        <x-button variant="primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            @term('add_action') @term('resource_singular')
        </x-button>
    </x-slot>

    <x-card>
        <div class="text-center py-12">
            <x-icon name="book-open" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">@term('resource_library_coming_soon_label')</p>
            <p class="text-gray-400 text-sm mt-1">@term('feature_under_development_label')</p>
        </div>
    </x-card>
</x-layouts.dashboard>
