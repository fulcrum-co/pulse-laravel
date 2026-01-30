<?php

namespace App\Livewire;

use App\Models\Program;
use Livewire\Component;

class ProgramDetail extends Component
{
    public Program $program;

    public function mount(Program $program): void
    {
        $this->program = $program;
    }

    public function render()
    {
        return view('livewire.program-detail')
            ->layout('layouts.dashboard', ['title' => 'Program Details']);
    }
}
