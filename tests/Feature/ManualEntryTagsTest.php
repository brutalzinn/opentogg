<?php

namespace Tests\Feature;

use App\Livewire\ManualEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualEntryTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_manual_entry_parses_and_links_tags(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ManualEntry::class)
            ->set('description', 'Write report #focus #deep')
            ->set('startedAt', now()->subHour()->format('Y-m-d\TH:i'))
            ->set('stoppedAt', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasNoErrors();

        $entry = $user->timeEntries()->with('tags')->firstOrFail();
        $this->assertSame('Write report', $entry->description);
        $this->assertEqualsCanonicalizing(['focus', 'deep'], $entry->tags->pluck('name')->all());
        $this->assertDatabaseHas('tags', ['user_id' => $user->id, 'name' => 'focus']);
        $this->assertDatabaseHas('tags', ['user_id' => $user->id, 'name' => 'deep']);
    }
}
