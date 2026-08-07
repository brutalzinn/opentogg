<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the active personalization preference set (locale / timezone /
 * currency / hourly rate).
 *
 * Preferences::current() → the authenticated user's preferences, or the
 * configured defaults when there is no authenticated user (jobs, console,
 * guests). Preferences::for($user) → a specific user's preferences, so any
 * context can render data in the *owner's* preferences rather than the
 * viewer's.
 */
class Preferences
{
    /**
     * Preferences for the currently authenticated user, or defaults.
     *
     * @return array{locale:string,timezone:string,currency:string,hourly_rate:float}
     */
    public static function current(): array
    {
        return self::for(Auth::user());
    }

    /**
     * Preferences for a specific user (or defaults when null / values missing).
     *
     * @return array{locale:string,timezone:string,currency:string,hourly_rate:float}
     */
    public static function for(?User $user): array
    {
        $defaults = config('preferences.defaults');

        $locale = $user?->locale ?: $defaults['locale'];

        // Guard against unknown/legacy stored locales (e.g. the old "en" default).
        if (! array_key_exists($locale, config('preferences.locales'))) {
            $locale = $defaults['locale'];
        }

        return [
            'locale' => $locale,
            'timezone' => $user?->timezone ?: $defaults['timezone'],
            'currency' => $user?->currency ?: $defaults['currency'],
            'hourly_rate' => (float) ($user?->hourly_rate ?? $defaults['hourly_rate']),
        ];
    }

    /**
     * The Laravel translation directory name (lang/<app>) for a BCP-47 locale.
     */
    public static function appLocale(string $locale): string
    {
        return config("preferences.locales.$locale.app", config('app.fallback_locale'));
    }

    /**
     * The ICU locale (for intl NumberFormatter) for a BCP-47 locale.
     */
    public static function icuLocale(string $locale): string
    {
        return config("preferences.locales.$locale.icu", 'en_US');
    }
}
