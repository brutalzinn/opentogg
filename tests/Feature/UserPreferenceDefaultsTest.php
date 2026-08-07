<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_created_directly_gets_personalization_defaults(): void
    {
        // Bypass the factory (which sets values explicitly) to prove the
        // model's creating hook applies defaults on a bare create.
        $user = User::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'google_id' => '999',
        ]);

        $this->assertSame('pt-BR', $user->locale);
        $this->assertSame('America/Sao_Paulo', $user->timezone);
        $this->assertSame('BRL', $user->currency);
        $this->assertSame('0.00', $user->hourly_rate);
    }

    public function test_factory_user_has_default_preferences(): void
    {
        $user = User::factory()->create();

        $this->assertSame('pt-BR', $user->locale);
        $this->assertSame('America/Sao_Paulo', $user->timezone);
        $this->assertSame('BRL', $user->currency);
    }
}
