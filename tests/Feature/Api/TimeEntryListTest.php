<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryListTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_includes_the_running_entry_at_the_top(): void
    {
        $user = User::factory()->create();
        $user->timeEntries()->create([
            'description' => 'done',
            'started_at' => now()->subHours(2),
            'stopped_at' => now()->subHour(),
        ]);
        $running = $user->timeEntries()->create([
            'description' => 'in progress',
            'started_at' => now()->subMinutes(5),
            'stopped_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/time-entries');

        $response->assertOk();
        $response->assertJsonPath('total', 2);
        $response->assertJsonPath('data.0.external_id', $running->external_id);
        $response->assertJsonPath('data.0.stopped_at', null);
    }

    public function test_index_is_scoped_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $other->timeEntries()->create([
            'description' => 'someone else',
            'started_at' => now()->subMinutes(5),
            'stopped_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/time-entries');

        $response->assertOk();
        $response->assertJsonPath('total', 0);
    }
}
