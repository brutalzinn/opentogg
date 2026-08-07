<?php

namespace Tests\Feature;

use App\Livewire\Reports;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_entries_are_grouped_by_the_users_local_day(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/Sao_Paulo',
        ]);

        // 01:00 UTC on Jan 15 is 22:00 on Jan 14 in São Paulo, so this entry
        // belongs to the user's Jan 14, not the UTC Jan 15.
        $user->timeEntries()->create([
            'started_at' => Carbon::parse('2025-01-15T01:00:00Z'),
            'stopped_at' => Carbon::parse('2025-01-15T02:00:00Z'),
        ]);

        Livewire::actingAs($user)
            ->test(Reports::class)
            ->set('period', 'custom')
            ->set('startDate', '2025-01-14')
            ->set('endDate', '2025-01-14')
            ->assertViewHas('totalEntries', 1)
            ->assertViewHas('totalHours', 1);
    }
}
