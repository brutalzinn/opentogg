<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FormatTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $locale, string $timezone, string $currency): User
    {
        return User::factory()->create([
            'locale' => $locale,
            'timezone' => $timezone,
            'currency' => $currency,
        ]);
    }

    /** Normalize the various (non-breaking) spaces intl emits. */
    private function normalize(string $value): string
    {
        return str_replace(["\xc2\xa0", "\xe2\x80\xaf"], ' ', $value);
    }

    public function test_money_is_formatted_per_locale_and_currency(): void
    {
        $ptBr = $this->user('pt-BR', 'America/Sao_Paulo', 'BRL');
        $enUs = $this->user('en-US', 'America/New_York', 'USD');

        $this->assertSame('R$ 1.234,56', $this->normalize(Format::money(1234.56, $ptBr)));
        $this->assertSame('$1,234.56', $this->normalize(Format::money(1234.56, $enUs)));
    }

    public function test_numbers_are_formatted_per_locale(): void
    {
        $ptBr = $this->user('pt-BR', 'America/Sao_Paulo', 'BRL');
        $enUs = $this->user('en-US', 'America/New_York', 'USD');

        $this->assertSame('1.234,56', $this->normalize(Format::number(1234.56, 2, $ptBr)));
        $this->assertSame('1,234.56', $this->normalize(Format::number(1234.56, 2, $enUs)));
    }

    public function test_dates_are_converted_to_timezone_and_formatted_per_locale(): void
    {
        $ptBr = $this->user('pt-BR', 'America/Sao_Paulo', 'BRL');
        $enUs = $this->user('en-US', 'America/New_York', 'USD');

        $utc = Carbon::parse('2025-01-15T12:00:00Z');

        $this->assertSame('15/01/2025', Format::date($utc, $ptBr));
        $this->assertSame('01/15/2025', Format::date($utc, $enUs));
    }

    public function test_date_conversion_respects_local_day_boundary(): void
    {
        $ptBr = $this->user('pt-BR', 'America/Sao_Paulo', 'BRL');

        // 01:00 UTC is still the previous day (22:00) in São Paulo.
        $utc = Carbon::parse('2025-01-15T01:00:00Z');

        $this->assertSame('14/01/2025', Format::date($utc, $ptBr));
    }
}
