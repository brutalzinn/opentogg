<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use NumberFormatter;

/**
 * Locale / timezone / currency aware display formatting.
 *
 * All methods default to the active user's preferences
 * (Preferences::current()) but accept an explicit User to render another
 * user's data in *their* preferences. Values stored in UTC are converted to
 * the target timezone for display; storage is never mutated here.
 */
class Format
{
    /**
     * Format a monetary amount in the user's currency + locale.
     * e.g. pt-BR/BRL → "R$ 1.234,56", en-US/USD → "$1,234.56".
     */
    public static function money(float|int|string|null $amount, ?User $user = null): string
    {
        $prefs = self::prefs($user);
        $formatter = new NumberFormatter(Preferences::icuLocale($prefs['locale']), NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $amount, $prefs['currency']);
    }

    /**
     * Format a plain number in the user's locale.
     * e.g. pt-BR → "1.234,56", en-US → "1,234.56".
     */
    public static function number(float|int|string|null $value, int $decimals = 2, ?User $user = null): string
    {
        $prefs = self::prefs($user);
        $formatter = new NumberFormatter(Preferences::icuLocale($prefs['locale']), NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return $formatter->format((float) $value);
    }

    /**
     * Convert a UTC datetime to the user's timezone and format date + time.
     */
    public static function dateTime(CarbonInterface|string|null $value, ?User $user = null): string
    {
        return self::render($value, 'datetime', $user);
    }

    /**
     * Convert a UTC datetime to the user's timezone and format the date only.
     */
    public static function date(CarbonInterface|string|null $value, ?User $user = null): string
    {
        return self::render($value, 'date', $user);
    }

    /**
     * Convert a UTC datetime to the user's timezone and format the time only.
     */
    public static function time(CarbonInterface|string|null $value, ?User $user = null): string
    {
        return self::render($value, 'time', $user);
    }

    /**
     * The user's display timezone (helper for callers doing their own math).
     */
    public static function timezone(?User $user = null): string
    {
        return self::prefs($user)['timezone'];
    }

    private static function render(CarbonInterface|string|null $value, string $part, ?User $user): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $prefs = self::prefs($user);
        $pattern = config("preferences.locales.{$prefs['locale']}.$part");

        return Carbon::parse($value)->timezone($prefs['timezone'])->format($pattern);
    }

    /**
     * @return array{locale:string,timezone:string,currency:string,hourly_rate:float}
     */
    private static function prefs(?User $user): array
    {
        return $user ? Preferences::for($user) : Preferences::current();
    }
}
