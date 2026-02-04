<?php

namespace App\Livewire\Components;

use Livewire\Attributes\On;
use Livewire\Component;

class ShareModal extends Component
{
    public bool $show = false;
    public string $type = ''; // 'resource', 'course', or 'provider'
    public ?int $itemId = null;
    public string $title = '';
    public string $publicUrl = '';
    public string $embedCode = '';
    public int $embedWidth = 800;
    public int $embedHeight = 600;
    public bool $isPublic = false;

    #[On('open-share-modal')]
    public function open(string $type, int $id, string $title, bool $isPublic = false): void
    {
        $this->type = $type;
        $this->itemId = $id;
        $this->title = $title;
        $this->isPublic = $isPublic;
        $this->generateUrls();
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->reset(['type', 'itemId', 'title', 'publicUrl', 'embedCode']);
    }

    public function updatedEmbedWidth(): void
    {
        $this->generateEmbedCode();
    }

    public function updatedEmbedHeight(): void
    {
        $this->generateEmbedCode();
    }

    protected function generateUrls(): void
    {
        $baseUrl = config('app.url');

        $this->publicUrl = match ($this->type) {
            'resource' => "{$baseUrl}/embed/resource/{$this->itemId}",
            'course' => "{$baseUrl}/embed/course/{$this->itemId}",
            'provider' => "{$baseUrl}/embed/provider/{$this->itemId}",
            default => "{$baseUrl}/embed/resource/{$this->itemId}",
        };

        $this->generateEmbedCode();
    }

    protected function generateEmbedCode(): void
    {
        $this->embedCode = sprintf(
            '<iframe src="%s" width="%d" height="%d" frameborder="0" allow="fullscreen" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"></iframe>',
            $this->publicUrl,
            $this->embedWidth,
            $this->embedHeight
        );
    }

    public function render()
    {
        return view('livewire.components.share-modal');
    }
}
