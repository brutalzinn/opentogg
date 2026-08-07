<div>
    <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ __('app.settings_prefs_title') }}</h2>

    @if(session('success'))
        <div class="bg-success/20 text-success p-3 mb-4 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-surface-raised p-3 sm:p-4 rounded-xl">
        <p class="text-text-secondary text-sm mb-4">{{ __('app.settings_prefs_description') }}</p>

        <form wire:submit="save" class="space-y-4">
            {{-- Language --}}
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.settings_locale') }}</label>
                <select
                    wire:model="locale"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                >
                    @foreach($locales as $code => $meta)
                        <option value="{{ $code }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                @error('locale') <p class="text-danger text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Timezone --}}
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.settings_timezone') }}</label>
                <select
                    wire:model="timezone"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                >
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </select>
                @error('timezone') <p class="text-danger text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Currency --}}
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.settings_currency') }}</label>
                <select
                    wire:model="currency"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                >
                    @foreach($currencies as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
                @error('currency') <p class="text-danger text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Hourly rate --}}
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.settings_hourly_rate') }}</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    wire:model="hourlyRate"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                >
                <p class="text-text-secondary text-xs mt-1">{{ __('app.settings_hourly_rate_hint') }}</p>
                @error('hourlyRate') <p class="text-danger text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button
                type="submit"
                class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 rounded-xl [touch-action:manipulation]"
            >
                {{ __('app.settings_prefs_save') }}
            </button>
        </form>
    </div>
</div>
