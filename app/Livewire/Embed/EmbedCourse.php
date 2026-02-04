<?php

namespace App\Livewire\Embed;

use App\Models\MiniCourse;
use Livewire\Component;

class EmbedCourse extends Component
{
    public ?MiniCourse $course = null;
    public bool $notFound = false;
    public int $currentStepIndex = 0;

    public function mount(int $course): void
    {
        $this->course = MiniCourse::with(['steps', 'organization'])
            ->where('id', $course)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->whereIn('visibility', [MiniCourse::VISIBILITY_PUBLIC, MiniCourse::VISIBILITY_GATED])
            ->first();

        if (! $this->course) {
            $this->notFound = true;
        }
    }

    public function nextStep(): void
    {
        if ($this->course && $this->currentStepIndex < $this->course->steps->count() - 1) {
            $this->currentStepIndex++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStepIndex > 0) {
            $this->currentStepIndex--;
        }
    }

    public function goToStep(int $index): void
    {
        if ($this->course && $index >= 0 && $index < $this->course->steps->count()) {
            $this->currentStepIndex = $index;
        }
    }

    public function render()
    {
        return view('livewire.embed.embed-course')
            ->layout('layouts.embed', [
                'title' => $this->course?->title ?? 'Course Not Found',
            ]);
    }
}
