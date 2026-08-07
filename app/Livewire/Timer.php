<?php

namespace App\Livewire;

use App\Jobs\DispatchGoalWebhook;
use App\Livewire\Concerns\HandlesEntryTags;
use App\Models\Goal;
use App\Support\Preferences;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Timer extends Component
{
    use HandlesEntryTags;

    public ?string $description = '';

    public ?int $vectorId = null;

    public ?int $runningEntryId = null;

    public bool $isRunning = false;

    public function mount(): void
    {
        $entry = Auth::user()->timeEntries()
            ->with('tags')
            ->whereNull('stopped_at')
            ->first();

        if ($entry) {
            $this->runningEntryId = $entry->id;
            $this->isRunning = true;
            $this->description = $this->rebuildDescriptionWithTags(
                $entry->description,
                $entry->tags->pluck('name')->toArray()
            );
            $this->vectorId = $entry->vector_id;
        }
    }

    public function start(): void
    {
        $cleanDescription = $this->extractAndSyncTags($this->description);

        $entry = Auth::user()->timeEntries()->create([
            'description' => $cleanDescription ?: null,
            'vector_id' => $this->vectorId ?: null,
            'started_at' => now(),
            'stopped_at' => null,
        ]);

        $this->syncTagsToEntry($entry);

        $this->runningEntryId = $entry->id;
        $this->isRunning = true;

        // Keep #tags visible in the input for visual feedback
        $this->description = $this->rebuildDescriptionWithTags($cleanDescription);

        $this->dispatch('timer-started', startedAt: (int) $entry->started_at->timestamp);
    }

    #[On('continue-entry')]
    public function continueEntry(int $entryId): void
    {
        if ($this->isRunning) {
            $this->stop();
        }

        $original = Auth::user()->timeEntries()->with('tags')->findOrFail($entryId);

        $this->description = $this->rebuildDescriptionWithTags(
            $original->description,
            $original->tags->pluck('name')->toArray()
        );
        $this->vectorId = $original->vector_id;

        $entry = Auth::user()->timeEntries()->create([
            'description' => $original->description,
            'vector_id' => $original->vector_id,
            'started_at' => now(),
            'stopped_at' => null,
        ]);

        if ($original->tags->isNotEmpty()) {
            $entry->tags()->sync($original->tags->pluck('id'));
        }

        $this->runningEntryId = $entry->id;
        $this->isRunning = true;

        $this->dispatch('timer-started', startedAt: (int) $entry->started_at->timestamp);
        $this->dispatch('entry-stopped');
    }

    public function stop(): void
    {
        if (! $this->runningEntryId) {
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

        $this->checkGoals();
    }

    private function checkGoals(): void
    {
        $goals = Auth::user()->goals()->with('vector')->get();

        foreach ($goals as $goal) {
            $hours = $this->calculateHoursForGoal($goal);
            $achieved = $goal->operator === 'gte'
                ? $hours >= (float) $goal->target_hours
                : $hours <= (float) $goal->target_hours;

            if ($achieved) {
                $alreadyAchievedThisPeriod = $goal->last_achieved_at
                    && $this->isWithinCurrentPeriod($goal->last_achieved_at, $goal->period);

                if (! $alreadyAchievedThisPeriod) {
                    $goal->update(['last_achieved_at' => now()]);

                    $vectorName = $goal->vector?->name ?? __('app.goals_any_vector');
                    $message = __('app.goals_toast_achieved', [
                        'vector' => $vectorName,
                        'hours' => $goal->target_hours,
                        'period' => __('app.goals_'.$goal->period),
                    ]);
                    $this->dispatch('goal-achieved', message: $message);

                    if ($goal->webhook_url) {
                        DispatchGoalWebhook::dispatch($goal, $hours);
                    }
                }
            }
        }
    }

    private function calculateHoursForGoal(Goal $goal): float
    {
        $query = Auth::user()->timeEntries()->whereNotNull('stopped_at');

        if ($goal->vector_id) {
            $query->where('vector_id', $goal->vector_id);
        }

        // Goal periods track the user's local day/week/month; stored timestamps
        // are UTC, so convert the local-period boundaries to UTC instants.
        $now = now(Preferences::current()['timezone']);
        [$start, $end] = match ($goal->period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $query->whereBetween('started_at', [$start->utc(), $end->utc()]);

        return $query->get()->sum(fn ($entry) => $entry->started_at->diffInSeconds($entry->stopped_at)) / 3600;
    }

    private function isWithinCurrentPeriod(Carbon $date, string $period): bool
    {
        $tz = Preferences::current()['timezone'];
        $now = now($tz);
        $local = $date->copy()->timezone($tz);

        return match ($period) {
            'daily' => $local->isSameDay($now),
            'weekly' => $local->isSameWeek($now),
            'monthly' => $local->isSameMonth($now),
        };
    }

    public function syncState(): void
    {
        $entry = Auth::user()->timeEntries()
            ->with('tags')
            ->whereNull('stopped_at')
            ->first();

        if ($entry && ! $this->isRunning) {
            // Timer was started externally (API, another device)
            $this->runningEntryId = $entry->id;
            $this->isRunning = true;
            $this->description = $this->rebuildDescriptionWithTags(
                $entry->description,
                $entry->tags->pluck('name')->toArray()
            );
            $this->vectorId = $entry->vector_id;
            $this->dispatch('timer-started', startedAt: (int) $entry->started_at->timestamp);
        } elseif (! $entry && $this->isRunning) {
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
        if (! $this->runningEntryId) {
            return;
        }

        $entry = Auth::user()->timeEntries()->find($this->runningEntryId);
        if ($entry) {
            $cleanDescription = $this->extractAndSyncTags($this->description);
            $entry->description = $cleanDescription ?: null;
            $entry->save();
            $this->syncTagsToEntry($entry);
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

        $tags = Auth::user()->tags()->orderBy('name')->pluck('name');

        return view('livewire.timer', [
            'vectors' => $vectors,
            'startedAtUnix' => $startedAtUnix,
            'tags' => $tags,
        ]);
    }
}
