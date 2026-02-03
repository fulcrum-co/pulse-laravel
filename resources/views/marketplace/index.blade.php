<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('marketplace_label') }}">
    <x-card>
        <div class="text-center py-12">
            <x-icon name="shopping-bag" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">@term('marketplace_coming_soon_label')</p>
            <p class="text-gray-400 text-sm mt-1">@term('feature_under_development_label')</p>
        </div>
    </x-card>
</x-layouts.dashboard>
