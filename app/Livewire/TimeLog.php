<?php

namespace App\Livewire;

use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TimeLog extends Component
{
    public ?int $editingEntryId = null;
    public ?string $editDescription = null;
    public ?int $editVectorId = null;
    public ?string $editStartedAt = null;
    public ?string $editStoppedAt = null;

    #[On('entry-stopped')]
    public function refreshEntries(): void
    {
        // Re-render triggers fresh query
    }

    public function startFullEdit(int $id): void
    {
        $entry = Auth::user()->timeEntries()->findOrFail($id);

        $this->editingEntryId = $entry->id;
        $this->editDescription = $entry->description ?? '';
        $this->editVectorId = $entry->vector_id;
        $this->editStartedAt = $entry->started_at->format('Y-m-d\TH:i');
        $this->editStoppedAt = $entry->stopped_at->format('Y-m-d\TH:i');
    }

    public function saveFullEdit(): void
    {
        $this->validate([
            'editStartedAt' => 'required|date',
            'editStoppedAt' => 'required|date|after:editStartedAt',
        ]);

        $entry = Auth::user()->timeEntries()->findOrFail($this->editingEntryId);
        $entry->description = $this->editDescription ?: null;
        $entry->vector_id = $this->editVectorId ?: null;
        $entry->started_at = \Illuminate\Support\Carbon::parse($this->editStartedAt);
        $entry->stopped_at = \Illuminate\Support\Carbon::parse($this->editStoppedAt);
        $entry->save();

        $this->cancelFullEdit();
    }

    public function cancelFullEdit(): void
    {
        $this->editingEntryId = null;
        $this->editDescription = null;
        $this->editVectorId = null;
        $this->editStartedAt = null;
        $this->editStoppedAt = null;
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

        $vectors = Auth::user()->vectors()->orderBy('name')->get();

        return view('livewire.time-log', [
            'entries' => $entries,
            'vectors' => $vectors,
        ]);
    }
}
