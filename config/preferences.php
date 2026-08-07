<?php

/*
|--------------------------------------------------------------------------
| User Personalization Preferences
|--------------------------------------------------------------------------
|
| Single source of truth for user locale / timezone / currency handling.
| Consumed by the Preferences resolver, the Format helper, the settings UI,
| the API validation, and the SetUserPreferences middleware.
|
| Stored user values use BCP-47 locale tags (pt-BR, en-US). Each maps to:
|   - "app": the Laravel translation directory name (lang/<app>)
|   - "icu": the ICU locale for intl NumberFormatter / number formatting
|   - "date" / "time" / "datetime": PHP date() patterns for that locale
|
*/

return [

    'defaults' => [
        'locale' => 'pt-BR',
        'timezone' => 'America/Sao_Paulo',
        'currency' => 'BRL',
        'hourly_rate' => 0,
    ],

    // Supported locales (pt-BR is default, en-US is the required alternative/fallback).
    'locales' => [
        'pt-BR' => [
            'label' => 'Português (Brasil)',
            'app' => 'pt_BR',
            'icu' => 'pt_BR',
            'date' => 'd/m/Y',
            'time' => 'H:i',
            'datetime' => 'd/m/Y H:i',
        ],
        'en-US' => [
            'label' => 'English (US)',
            'app' => 'en',
            'icu' => 'en_US',
            'date' => 'm/d/Y',
            'time' => 'h:i A',
            'datetime' => 'm/d/Y h:i A',
        ],
    ],

    // Supported currencies (ISO 4217).
    'currencies' => ['BRL', 'USD', 'EUR'],

];
