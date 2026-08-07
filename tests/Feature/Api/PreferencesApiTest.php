<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_current_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.locale', 'pt-BR')
            ->assertJsonPath('data.timezone', 'America/Sao_Paulo')
            ->assertJsonPath('data.currency', 'BRL');
    }

    public function test_updates_preferences_and_applies_immediately(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/preferences', [
                'locale' => 'en-US',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'hourly_rate' => 120,
            ])
            ->assertOk()
            ->assertJsonPath('data.locale', 'en-US')
            ->assertJsonPath('data.currency', 'USD');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/preferences')
            ->assertOk()
            ->assertJsonPath('data.timezone', 'America/New_York');
    }

    public function test_rejects_invalid_values_with_english_messages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/preferences', [
                'locale' => 'fr-FR',
                'currency' => 'GBP',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['locale', 'currency']);

        // API stays English regardless of the user's locale.
        $this->assertStringContainsString('selected', $response->json('errors.locale.0'));
    }
}
