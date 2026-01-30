<x-layouts.dashboard title="Send Survey - {{ $survey->title }}">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('surveys.show', $survey) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to {{ $survey->title }}
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Send Survey</h1>
        <p class="text-gray-500 mt-1">Deliver "{{ $survey->title }}" to students via multiple channels</p>
    </div>

    <livewire:survey.delivery-manager :survey="$survey" />
</x-layouts.dashboard>
