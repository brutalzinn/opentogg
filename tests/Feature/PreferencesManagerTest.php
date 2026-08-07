<?php

namespace Tests\Feature;

use App\Livewire\PreferencesManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PreferencesManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_saves_valid_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PreferencesManager::class)
            ->set('locale', 'en-US')
            ->set('timezone', 'America/New_York')
            ->set('currency', 'USD')
            ->set('hourlyRate', '120')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings'));

        $user->refresh();
        $this->assertSame('en-US', $user->locale);
        $this->assertSame('America/New_York', $user->timezone);
        $this->assertSame('USD', $user->currency);
        $this->assertSame('120.00', $user->hourly_rate);
    }

    public function test_rejects_invalid_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PreferencesManager::class)
            ->set('locale', 'fr-FR')
            ->set('currency', 'GBP')
            ->set('hourlyRate', '-5')
            ->call('save')
            ->assertHasErrors(['locale', 'currency', 'hourlyRate']);

        $user->refresh();
        $this->assertSame('pt-BR', $user->locale);
    }
}
