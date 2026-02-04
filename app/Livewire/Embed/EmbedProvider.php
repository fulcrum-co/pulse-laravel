<?php

namespace App\Livewire\Embed;

use App\Models\Provider;
use Livewire\Component;

class EmbedProvider extends Component
{
    public ?Provider $provider = null;

    public bool $notFound = false;

    public function mount(int $provider): void
    {
        $this->provider = Provider::with('organization')
            ->where('id', $provider)
            ->where('is_public', true)
            ->where('active', true)
            ->first();

        if (! $this->provider) {
            $this->notFound = true;
        }
    }

    public function render()
    {
        return view('livewire.embed.embed-provider')
            ->layout('layouts.embed', [
                'title' => $this->provider?->name ?? 'Provider Not Found',
            ]);
    }
}
