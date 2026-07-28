<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesEntryTags;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManualEntry extends Component
{
    use HandlesEntryTags;

    public bool $showForm = false;

    public ?string $description = '';

    public ?int $vectorId = null;

    public ?string $startedAt = '';

    public ?string $stoppedAt = '';

    public function openForm(): void
    {
        $now = now();
        $this->startedAt = $now->copy()->startOfHour()->format('Y-m-d\TH:i');
        $this->stoppedAt = $now->copy()->startOfHour()->addHour()->format('Y-m-d\TH:i');
        $this->description = '';
        $this->vectorId = null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'startedAt' => 'required|date',
            'stoppedAt' => 'required|date|after:startedAt',
        ]);

        $cleanDescription = $this->extractAndSyncTags($this->description);

        $entry = Auth::user()->timeEntries()->create([
            'description' => $cleanDescription ?: null,
            'vector_id' => $this->vectorId ?: null,
            'started_at' => Carbon::parse($this->startedAt),
            'stopped_at' => Carbon::parse($this->stoppedAt),
        ]);

        $this->syncTagsToEntry($entry);

        $this->showForm = false;
        $this->reset(['description', 'vectorId', 'startedAt', 'stoppedAt']);
        $this->dispatch('entry-stopped');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->reset(['description', 'vectorId', 'startedAt', 'stoppedAt']);
    }

    public function render()
    {
        $vectors = Auth::user()->vectors()->orderBy('name')->get();
        $tags = Auth::user()->tags()->orderBy('name')->pluck('name');

        return view('livewire.manual-entry', [
            'vectors' => $vectors,
            'tags' => $tags,
        ]);
    }
}
