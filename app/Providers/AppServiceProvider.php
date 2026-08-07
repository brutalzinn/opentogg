<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Preference-aware display directives. All respect the active user's
        // locale / timezone / currency (see App\Support\Format).
        Blade::directive('money', fn ($e) => "<?php echo \App\Support\Format::money($e); ?>");
        Blade::directive('lnumber', fn ($e) => "<?php echo \App\Support\Format::number($e); ?>");
        Blade::directive('ldate', fn ($e) => "<?php echo \App\Support\Format::date($e); ?>");
        Blade::directive('ltime', fn ($e) => "<?php echo \App\Support\Format::time($e); ?>");
        Blade::directive('ldatetime', fn ($e) => "<?php echo \App\Support\Format::dateTime($e); ?>");
    }
}
