<?php

namespace App\Livewire;

use App\Jobs\DispatchGoalWebhook;
use App\Models\Goal;
use Illuminate\Support\Carbon;
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

        $now = now();
        match ($goal->period) {
            'daily' => $query->whereDate('started_at', $now->toDateString()),
            'weekly' => $query->whereBetween('started_at', [$now->startOfWeek(), $now->copy()->endOfWeek()]),
            'monthly' => $query->whereMonth('started_at', $now->month)->whereYear('started_at', $now->year),
        };

        return $query->get()->sum(fn ($entry) => $entry->started_at->diffInSeconds($entry->stopped_at)) / 3600;
    }

    private function isWithinCurrentPeriod(Carbon $date, string $period): bool
    {
        $now = now();

        return match ($period) {
            'daily' => $date->isSameDay($now),
            'weekly' => $date->isSameWeek($now),
            'monthly' => $date->isSameMonth($now),
        };
    }

    public function syncState(): void
    {
        $entry = Auth::user()->timeEntries()
            ->whereNull('stopped_at')
            ->first();

        if ($entry && ! $this->isRunning) {
            // Timer was started externally (API, another device)
            $this->runningEntryId = $entry->id;
            $this->isRunning = true;
            $this->description = $entry->description ?? '';
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
