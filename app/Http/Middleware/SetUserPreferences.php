<?php

namespace App\Http\Middleware;

use App\Support\Preferences;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the active user's locale to the web request so that translations
 * (__()) and translated Carbon output render in the right language.
 *
 * Storage timezone is intentionally NOT changed here — timestamps stay in UTC
 * and are converted for display only via App\Support\Format. This middleware
 * runs on the "web" group only; the API keeps English + UTC.
 */
class SetUserPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Preferences::current()['locale'];
        $appLocale = Preferences::appLocale($locale);

        App::setLocale($appLocale);
        Carbon::setLocale($appLocale);

        return $next($request);
    }
}
