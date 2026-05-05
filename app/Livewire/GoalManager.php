<?php

namespace App\Livewire;

use App\Models\Goal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GoalManager extends Component
{
    public ?int $vectorId = null;

    public string $operator = 'gte';

    public ?string $targetHours = null;

    public string $period = 'daily';

    public ?string $webhookUrl = null;

    public ?int $editingId = null;

    public ?int $editVectorId = null;

    public string $editOperator = 'gte';

    public ?string $editTargetHours = null;

    public string $editPeriod = 'daily';

    public ?string $editWebhookUrl = null;

    public function create(): void
    {
        $this->validate([
            'targetHours' => 'required|numeric|min:0.01',
            'operator' => 'required|in:gte,lte',
            'period' => 'required|in:daily,weekly,monthly',
            'webhookUrl' => 'nullable|url',
        ]);

        Auth::user()->goals()->create([
            'vector_id' => $this->vectorId ?: null,
            'operator' => $this->operator,
            'target_hours' => $this->targetHours,
            'period' => $this->period,
            'webhook_url' => $this->webhookUrl ?: null,
        ]);

        $this->reset(['vectorId', 'operator', 'targetHours', 'period', 'webhookUrl']);
        $this->operator = 'gte';
        $this->period = 'daily';
    }

    public function startEdit(int $id): void
    {
        $goal = Auth::user()->goals()->findOrFail($id);

        $this->editingId = $goal->id;
        $this->editVectorId = $goal->vector_id;
        $this->editOperator = $goal->operator;
        $this->editTargetHours = $goal->target_hours;
        $this->editPeriod = $goal->period;
        $this->editWebhookUrl = $goal->webhook_url;
    }

    public function save(): void
    {
        $this->validate([
            'editTargetHours' => 'required|numeric|min:0.01',
            'editOperator' => 'required|in:gte,lte',
            'editPeriod' => 'required|in:daily,weekly,monthly',
            'editWebhookUrl' => 'nullable|url',
        ]);

        $goal = Auth::user()->goals()->findOrFail($this->editingId);
        $goal->update([
            'vector_id' => $this->editVectorId ?: null,
            'operator' => $this->editOperator,
            'target_hours' => $this->editTargetHours,
            'period' => $this->editPeriod,
            'webhook_url' => $this->editWebhookUrl ?: null,
        ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        Auth::user()->goals()->findOrFail($id)->delete();
    }

    public function render()
    {
        $goals = Auth::user()->goals()->with('vector')->orderByDesc('createAt')->get();
        $vectors = Auth::user()->vectors()->orderBy('name')->get();

        // Calculate current progress for each goal
        $goalsWithProgress = $goals->map(function (Goal $goal) {
            $hours = $this->calculateHoursForGoal($goal);
            $achieved = $goal->operator === 'gte'
                ? $hours >= (float) $goal->target_hours
                : $hours <= (float) $goal->target_hours;

            return [
                'goal' => $goal,
                'current_hours' => round($hours, 2),
                'achieved' => $achieved,
                'percentage' => $goal->operator === 'gte'
                    ? min(100, ($hours / max(0.01, (float) $goal->target_hours)) * 100)
                    : ($hours <= (float) $goal->target_hours ? 100 : max(0, (1 - ($hours - (float) $goal->target_hours) / max(0.01, (float) $goal->target_hours)) * 100)),
            ];
        });

        return view('livewire.goal-manager', [
            'goalsWithProgress' => $goalsWithProgress,
            'vectors' => $vectors,
        ]);
    }

    private function calculateHoursForGoal(Goal $goal): float
    {
        $query = Auth::user()->timeEntries()
            ->whereNotNull('stopped_at');

        if ($goal->vector_id) {
            $query->where('vector_id', $goal->vector_id);
        }

        $now = now();
        match ($goal->period) {
            'daily' => $query->whereDate('started_at', $now->toDateString()),
            'weekly' => $query->whereBetween('started_at', [$now->startOfWeek(), $now->copy()->endOfWeek()]),
            'monthly' => $query->whereMonth('started_at', $now->month)->whereYear('started_at', $now->year),
        };

        $totalSeconds = $query->get()->sum(function ($entry) {
            return $entry->started_at->diffInSeconds($entry->stopped_at);
        });

        return $totalSeconds / 3600;
    }
}
