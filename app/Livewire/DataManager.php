<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class DataManager extends Component
{
    use WithFileUploads;

    public $importFile;

    public function render()
    {
        return view('livewire.data-manager');
    }
}
