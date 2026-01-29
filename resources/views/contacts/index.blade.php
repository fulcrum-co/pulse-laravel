<x-layouts.dashboard title="Contacts">
    <x-slot name="actions">
        <x-button variant="primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Create Contact
        </x-button>
    </x-slot>

    <livewire:contact-list />
</x-layouts.dashboard>
