<?php

namespace App\Livewire;

use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Timer extends Component
{
    public ?string $description = '';
    public ?int $vectorId = null;
    public ?int $runningEntryId = null;
    public bool $isRunning = false;

    public function mount(): void
    {
        $entry = Auth::user()->timeEntries()
            ->whereNull('stopped_at')
            ->first();

        if ($entry) {
            $this->runningEntryId = $entry->id;
            $this->isRunning = true;
            $this->description = $entry->description ?? '';
            $this->vectorId = $entry->vector_id;
        }
    }

    public function start(): void
    {
        $entry = Auth::user()->timeEntries()->create([
            'description' => $this->description ?: null,
            'vector_id' => $this->vectorId ?: null,
            'started_at' => now(),
            'stopped_at' => null,
        ]);

        $this->runningEntryId = $entry->id;
        $this->isRunning = true;

        $this->dispatch('timer-started', startedAt: (int) $entry->started_at->timestamp);
    }

    public function stop(): void
    {
        if (!$this->runningEntryId) {
            return;
        }

        $entry = Auth::user()->timeEntries()->findOrFail($this->runningEntryId);
        $entry->stopped_at = now();
        $entry->save();

        $this->runningEntryId = null;
        $this->isRunning = false;
        $this->description = '';
        $this->vectorId = null;

        $this->dispatch('timer-stopped');
        $this->dispatch('entry-stopped');
    }

    public function syncState(): void
    {
        $entry = Auth::user()->timeEntries()
            ->whereNull('stopped_at')
            ->first();

        if ($entry && !$this->isRunning) {
            // Timer was started externally (API, another device)
            $this->runningEntryId = $entry->id;
            $this->isRunning = true;
            $this->description = $entry->description ?? '';
            $this->vectorId = $entry->vector_id;
            $this->dispatch('timer-started', startedAt: (int) $entry->started_at->timestamp);
        } elseif (!$entry && $this->isRunning) {
            // Timer was stopped externally (API, another device)
            $this->runningEntryId = null;
            $this->isRunning = false;
            $this->description = '';
            $this->vectorId = null;
            $this->dispatch('timer-stopped');
            $this->dispatch('entry-stopped');
        }
    }

    public function updateDescription(): void
    {
        if (!$this->runningEntryId) {
            return;
        }

        $entry = Auth::user()->timeEntries()->find($this->runningEntryId);
        if ($entry) {
            $entry->description = $this->description ?: null;
            $entry->save();
        }
    }

    public function render()
    {
        $vectors = Auth::user()->vectors()->orderBy('name')->get();
        $startedAtUnix = null;

        if ($this->runningEntryId) {
            $entry = Auth::user()->timeEntries()->find($this->runningEntryId);
            $startedAtUnix = $entry?->started_at?->timestamp;
        }

        return view('livewire.timer', [
            'vectors' => $vectors,
            'startedAtUnix' => $startedAtUnix,
        ]);
    }
}
