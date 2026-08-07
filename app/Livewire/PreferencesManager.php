<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PreferencesManager extends Component
{
    public string $locale = '';

    public string $timezone = '';

    public string $currency = '';

    public string $hourlyRate = '0';

    public function mount(): void
    {
        $user = Auth::user();
        $this->locale = $user->locale;
        $this->timezone = $user->timezone;
        $this->currency = $user->currency;
        $this->hourlyRate = (string) $user->hourly_rate;
    }

    public function save()
    {
        $validated = $this->validate([
            'locale' => ['required', Rule::in(array_keys(config('preferences.locales')))],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', Rule::in(config('preferences.currencies'))],
            'hourlyRate' => ['required', 'numeric', 'min:0'],
        ]);

        Auth::user()->update([
            'locale' => $validated['locale'],
            'timezone' => $validated['timezone'],
            'currency' => $validated['currency'],
            'hourly_rate' => $validated['hourlyRate'],
        ]);

        session()->flash('success', __('app.settings_prefs_saved'));

        // Full-page redirect so the SetUserPreferences middleware re-applies
        // the (possibly new) locale immediately.
        return $this->redirect(route('settings'), navigate: true);
    }

    public function render()
    {
        return view('livewire.preferences-manager', [
            'locales' => config('preferences.locales'),
            'currencies' => config('preferences.currencies'),
            'timezones' => timezone_identifiers_list(),
        ]);
    }
}
