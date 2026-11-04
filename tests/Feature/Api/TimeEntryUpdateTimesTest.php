<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryUpdateTimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_sets_started_at_and_stopped_at(): void
    {
        $user = User::factory()->create();
        $entry = $user->timeEntries()->create(['started_at' => now(), 'stopped_at' => now()]);

        $this->actingAs($user, 'sanctum')->putJson("/api/v1/time-entries/{$entry->external_id}", [
            'started_at' => '2026-11-04T10:34:00Z',
            'stopped_at' => '2026-11-04T10:40:00Z',
        ])->assertOk();

        $entry->refresh();
        $this->assertSame('2026-11-04 10:34:00', $entry->started_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-04 10:40:00', $entry->stopped_at->utc()->format('Y-m-d H:i:s'));
    }

    public function test_update_rejects_stop_before_start(): void
    {
        $user = User::factory()->create();
        $entry = $user->timeEntries()->create(['started_at' => now(), 'stopped_at' => now()]);

        $this->actingAs($user, 'sanctum')->putJson("/api/v1/time-entries/{$entry->external_id}", [
            'started_at' => '2026-11-04T10:40:00Z',
            'stopped_at' => '2026-11-04T10:34:00Z',
        ])->assertUnprocessable();
    }

    public function test_update_cannot_touch_another_users_entry(): void
    {
        $user = User::factory()->create();
        $entry = User::factory()->create()->timeEntries()->create(['started_at' => now(), 'stopped_at' => now()]);

        $this->actingAs($user, 'sanctum')->putJson("/api/v1/time-entries/{$entry->external_id}", [
            'started_at' => '2026-11-04T10:34:00Z',
        ])->assertNotFound();
    }
}
