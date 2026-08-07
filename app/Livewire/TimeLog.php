<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesEntryTags;
use App\Support\Preferences;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TimeLog extends Component
{
    use HandlesEntryTags;

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
        $entry = Auth::user()->timeEntries()->with('tags')->findOrFail($id);

        $this->editingEntryId = $entry->id;
        $this->editDescription = $this->rebuildDescriptionWithTags(
            $entry->description,
            $entry->tags->pluck('name')->toArray()
        );
        $this->editVectorId = $entry->vector_id;

        // datetime-local inputs are the user's local wall-clock time.
        $tz = Preferences::current()['timezone'];
        $this->editStartedAt = $entry->started_at->timezone($tz)->format('Y-m-d\TH:i');
        $this->editStoppedAt = $entry->stopped_at->timezone($tz)->format('Y-m-d\TH:i');
    }

    public function saveFullEdit(): void
    {
        $this->validate([
            'editStartedAt' => 'required|date',
            'editStoppedAt' => 'required|date|after:editStartedAt',
        ]);

        $cleanDescription = $this->extractAndSyncTags($this->editDescription);

        $tz = Preferences::current()['timezone'];
        $entry = Auth::user()->timeEntries()->findOrFail($this->editingEntryId);
        $entry->description = $cleanDescription ?: null;
        $entry->vector_id = $this->editVectorId ?: null;
        $entry->started_at = Carbon::parse($this->editStartedAt, $tz)->utc();
        $entry->stopped_at = Carbon::parse($this->editStoppedAt, $tz)->utc();
        $entry->save();

        $this->syncTagsToEntry($entry);

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
        // "Today" means the user's local day, expressed as UTC boundaries.
        $tz = Preferences::current()['timezone'];
        $dayStart = now($tz)->startOfDay()->utc();
        $dayEnd = now($tz)->endOfDay()->utc();

        $entries = Auth::user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->whereBetween('stopped_at', [$dayStart, $dayEnd])
            ->with(['vector', 'tags'])
            ->orderByDesc('updateAt')
            ->get();

        $vectors = Auth::user()->vectors()->orderBy('name')->get();
        $tags = Auth::user()->tags()->orderBy('name')->pluck('name');

        return view('livewire.time-log', [
            'entries' => $entries,
            'vectors' => $vectors,
            'tags' => $tags,
        ]);
    }
}
