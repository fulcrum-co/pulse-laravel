<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('alerts_notifications_label') }}">
    @livewire('alerts.alerts-hub')
</x-layouts.dashboard>
