<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('edit_action') }} {{ app(\App\Services\TerminologyService::class)->get('survey_singular') }}">
    <livewire:survey.survey-creator :survey-id="$survey->id" />
</x-layouts.dashboard>
