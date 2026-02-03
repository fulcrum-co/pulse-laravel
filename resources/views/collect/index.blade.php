<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('collect_label') }}">
    <x-card>
        <div class="text-center py-12">
            <x-icon name="collection" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">@term('collect_label') is coming soon.</p>
            <p class="text-gray-400 text-sm mt-1">This feature is under development.</p>
        </div>
    </x-card>
</x-layouts.dashboard>
