<?php

namespace App\Jobs;

use App\Models\Goal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class DispatchGoalWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Goal $goal,
        public float $currentHours,
    ) {}

    public function handle(): void
    {
        Http::timeout(10)->post($this->goal->webhook_url, [
            'event' => 'goal.achieved',
            'goal' => [
                'id' => $this->goal->external_id,
                'vector' => $this->goal->vector?->name,
                'operator' => $this->goal->operator,
                'target_hours' => $this->goal->target_hours,
                'period' => $this->goal->period,
                'current_hours' => round($this->currentHours, 2),
            ],
            'achieved_at' => now()->toIso8601String(),
        ]);
    }
}
