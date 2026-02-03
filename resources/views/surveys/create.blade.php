<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('create_action') }} {{ app(\App\Services\TerminologyService::class)->get('survey_singular') }}">
    <livewire:survey.survey-creator />
</x-layouts.dashboard>
