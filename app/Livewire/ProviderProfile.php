<?php

namespace App\Livewire;

use App\Models\Provider;
use Livewire\Component;

class ProviderProfile extends Component
{
    public Provider $provider;

    public function mount(Provider $provider): void
    {
        $this->provider = $provider;
    }

    public function render()
    {
        return view('livewire.provider-profile')
            ->layout('layouts.app');
    }
}
