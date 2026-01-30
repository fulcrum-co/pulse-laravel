<x-layouts.dashboard title="Visual Workflow Editor">
    <x-slot name="actions">
        <a href="{{ route('alerts.edit', $workflow) }}">
            <x-button variant="secondary">
                <x-icon name="cog-6-tooth" class="w-4 h-4 mr-2" />
                Simple Mode
            </x-button>
        </a>
        <a href="{{ route('alerts.index') }}">
            <x-button variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Back to Alerts
            </x-button>
        </a>
    </x-slot>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" style="height: calc(100vh - 200px);">
        <div
            id="workflow-canvas"
            data-workflow-id="{{ $workflow->id }}"
            data-workflow-name="{{ $workflow->name }}"
            data-nodes='@json($workflow->nodes ?? [])'
            data-edges='@json($workflow->edges ?? [])'
            class="h-full w-full"
        ></div>
    </div>

    @viteReactRefresh
    @vite('resources/js/workflow-builder/app.tsx')
</x-layouts.dashboard>
