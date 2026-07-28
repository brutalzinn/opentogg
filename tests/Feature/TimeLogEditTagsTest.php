<?php

namespace Tests\Feature;

use App\Livewire\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimeLogEditTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_an_entry_syncs_tags_from_the_description(): void
    {
        $user = User::factory()->create();
        $existing = $user->tags()->create(['name' => 'old']);
        $entry = $user->timeEntries()->create([
            'description' => 'Task',
            'started_at' => now()->subHour(),
            'stopped_at' => now(),
        ]);
        $entry->tags()->sync([$existing->id]);

        Livewire::actingAs($user)
            ->test(TimeLog::class)
            ->call('startFullEdit', $entry->id)
            ->assertSet('editDescription', 'Task #old')
            ->set('editDescription', 'Task updated #new')
            ->set('editStartedAt', now()->subHour()->format('Y-m-d\TH:i'))
            ->set('editStoppedAt', now()->format('Y-m-d\TH:i'))
            ->call('saveFullEdit')
            ->assertHasNoErrors();

        $entry->refresh()->load('tags');
        $this->assertSame('Task updated', $entry->description);
        $this->assertEqualsCanonicalizing(['new'], $entry->tags->pluck('name')->all());
        $this->assertDatabaseMissing('time_entry_tag', [
            'time_entry_id' => $entry->id,
            'tag_id' => $existing->id,
        ]);
    }
}
