<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TagManager extends Component
{
    public string $name = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->tags()->create([
            'name' => $this->name,
        ]);

        $this->name = '';
    }

    public function startEdit(int $id): void
    {
        $tag = Auth::user()->tags()->findOrFail($id);
        $this->editingId = $id;
        $this->editingName = $tag->name;
    }

    public function save(): void
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
        ]);

        $tag = Auth::user()->tags()->findOrFail($this->editingId);
        $tag->update(['name' => $this->editingName]);

        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        Auth::user()->tags()->findOrFail($id)->delete();
    }

    public function render()
    {
        $tags = Auth::user()->tags()->orderBy('name')->get();

        return view('livewire.tag-manager', [
            'tags' => $tags,
        ]);
    }
}
