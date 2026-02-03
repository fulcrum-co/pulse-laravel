<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('send_action') }} {{ app(\App\Services\TerminologyService::class)->get('survey_singular') }} - {{ $survey->title }}">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('surveys.show', $survey) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            @term('back_to_label') {{ $survey->title }}
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">@term('send_action') @term('survey_singular')</h1>
        <p class="text-gray-500 mt-1">@term('delivery_label') "{{ $survey->title }}" to @term('learner_plural') via multiple @term('channel_plural')</p>
    </div>

    <livewire:survey.delivery-manager :survey="$survey" />
</x-layouts.dashboard>
