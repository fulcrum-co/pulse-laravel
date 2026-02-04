<?php

namespace App\Livewire\Embed;

use App\Models\Resource;
use Livewire\Component;

class EmbedResource extends Component
{
    public ?Resource $resource = null;
    public bool $notFound = false;

    public function mount(int $resource): void
    {
        $this->resource = Resource::with('organization')
            ->where('id', $resource)
            ->where('is_public', true)
            ->where('active', true)
            ->first();

        if (! $this->resource) {
            $this->notFound = true;
        }
    }

    public function render()
    {
        return view('livewire.embed.embed-resource')
            ->layout('layouts.embed', [
                'title' => $this->resource?->title ?? 'Resource Not Found',
            ]);
    }
}
