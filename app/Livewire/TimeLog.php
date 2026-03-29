<?php

namespace App\Livewire;

use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TimeLog extends Component
{
    public ?string $editingDescription = null;
    public ?int $editingId = null;

    #[On('entry-stopped')]
    public function refreshEntries(): void
    {
        // Re-render triggers fresh query
    }

    public function startEdit(int $id, ?string $description): void
    {
        $this->editingId = $id;
        $this->editingDescription = $description ?? '';
    }

    public function saveDescription(int $id): void
    {
        $entry = Auth::user()->timeEntries()->findOrFail($id);
        $entry->description = $this->editingDescription ?: null;
        $entry->save();

        $this->editingId = null;
        $this->editingDescription = null;
    }

    public function delete(int $id): void
    {
        Auth::user()->timeEntries()->findOrFail($id)->delete();
    }

    public function render()
    {
        $entries = Auth::user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->whereDate('stopped_at', today())
            ->with('vector')
            ->orderByDesc('updateAt')
            ->get();

        return view('livewire.time-log', [
            'entries' => $entries,
        ]);
    }
}
