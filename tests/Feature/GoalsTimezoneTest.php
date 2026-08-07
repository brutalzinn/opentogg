<?php

namespace Tests\Feature;

use App\Livewire\GoalManager;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class GoalsTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_daily_goal_progress_uses_the_users_local_day(): void
    {
        // 00:30 UTC on Jan 16 is 21:30 on Jan 15 in São Paulo (UTC-3): still the
        // user's Jan 15, even though the UTC calendar day has already rolled over.
        Carbon::setTestNow('2025-01-16T00:30:00Z');

        $user = User::factory()->create([
            'timezone' => 'America/Sao_Paulo',
        ]);

        $goal = Goal::create([
            'user_id' => $user->id,
            'operator' => 'gte',
            'target_hours' => 1,
            'period' => 'daily',
        ]);

        // 21:00 UTC on Jan 15 = 18:00 São Paulo on Jan 15: the user's "today".
        // A UTC-based daily window would exclude this once the UTC day rolls,
        // making the evening's progress vanish.
        $user->timeEntries()->create([
            'started_at' => Carbon::parse('2025-01-15T21:00:00Z'),
            'stopped_at' => Carbon::parse('2025-01-15T22:00:00Z'),
        ]);

        Livewire::actingAs($user)
            ->test(GoalManager::class)
            ->assertViewHas('goalsWithProgress', function ($goals) {
                return $goals->first()['current_hours'] === 1.0;
            });
    }
}
