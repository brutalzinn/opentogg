<?php

namespace App\Livewire;

use App\Models\Vector;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VectorManager extends Component
{
    public string $name = '';
    public string $color = '#6B7280';

    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingColor = '#6B7280';

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
        ]);

        Auth::user()->vectors()->create([
            'name' => $this->name,
            'color' => $this->color,
        ]);

        $this->name = '';
        $this->color = '#6B7280';
    }

    public function startEdit(int $id): void
    {
        $vector = Auth::user()->vectors()->findOrFail($id);
        $this->editingId = $id;
        $this->editingName = $vector->name;
        $this->editingColor = $vector->color;
    }

    public function save(): void
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingColor' => 'required|string|size:7',
        ]);

        $vector = Auth::user()->vectors()->findOrFail($this->editingId);
        $vector->update([
            'name' => $this->editingName,
            'color' => $this->editingColor,
        ]);

        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        Auth::user()->vectors()->findOrFail($id)->delete();
    }

    public function render()
    {
        $vectors = Auth::user()->vectors()->orderBy('name')->get();

        return view('livewire.vector-manager', [
            'vectors' => $vectors,
        ]);
    }
}
