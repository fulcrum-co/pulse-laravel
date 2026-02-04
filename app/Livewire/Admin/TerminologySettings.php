<?php

namespace App\Livewire\Admin;

use App\Models\OrganizationSettings;
use App\Services\TerminologyService;
use Livewire\Attributes\On;
use Livewire\Component;

class TerminologySettings extends Component
{
    public array $terminology = [];
    public array $categories = [];
    public bool $hasChanges = false;

    protected TerminologyService $terminologyService;

    public function boot(TerminologyService $terminologyService): void
    {
        $this->terminologyService = $terminologyService;
    }

    public function mount(): void
    {
        $this->loadTerminology();
        $this->categories = OrganizationSettings::getTerminologyCategories();
    }

    public function loadTerminology(): void
    {
        $user = auth()->user();
        if (!$user?->org_id) {
            return;
        }

        $settings = OrganizationSettings::forOrganization($user->org_id);
        $allTerminology = $settings->getAllTerminology();

        // Build form data with current values
        $this->terminology = [];
        foreach (OrganizationSettings::DEFAULT_TERMINOLOGY as $key => $default) {
            $this->terminology[$key] = $allTerminology[$key] ?? $default;
        }

        $this->hasChanges = false;
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'terminology.')) {
            $this->hasChanges = true;
        }
    }

    #[On('save-settings')]
    public function save(): void
    {
        $user = auth()->user();
        if (!$user?->org_id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No organization found.',
            ]);
            return;
        }

        $settings = OrganizationSettings::forOrganization($user->org_id);

        // Only save values that differ from defaults
        $customTerminology = [];
        foreach ($this->terminology as $key => $value) {
            $default = OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? '';
            if ($value !== $default && !empty($value)) {
                $customTerminology[$key] = $value;
            }
        }

        $settings->terminology = $customTerminology;
        $settings->save();

        // Clear cache
        $this->terminologyService->clearCache($user->org_id);

        $this->hasChanges = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Terminology settings saved successfully.',
        ]);
    }

    public function resetToDefaults(): void
    {
        $user = auth()->user();
        if (!$user?->org_id) {
            return;
        }

        $settings = OrganizationSettings::forOrganization($user->org_id);
        $settings->resetTerminology();

        // Clear cache
        $this->terminologyService->clearCache($user->org_id);

        // Reload form
        $this->terminology = OrganizationSettings::DEFAULT_TERMINOLOGY;
        $this->hasChanges = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Terminology reset to defaults.',
        ]);
    }

    public function resetField(string $key): void
    {
        $default = OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? '';
        $this->terminology[$key] = $default;
        $this->hasChanges = true;
    }

    public function render()
    {
        return view('livewire.admin.terminology-settings');
    }
}
