<div>
    {{-- FAB Button --}}
    @unless($showForm)
        <button
            wire:click="openForm"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-accent hover:bg-accent-hover text-surface rounded-full shadow-lg flex items-center justify-center text-3xl font-light [touch-action:manipulation]"
            aria-label="{{ __('app.manual_entry_title') }}"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </button>
    @endunless

    {{-- Bottom Sheet --}}
    @if($showForm)
        {{-- Backdrop --}}
        <div
            wire:click="cancel"
            class="fixed inset-0 bg-black/50 z-40"
            x-data
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        {{-- Sheet --}}
        <div
            class="fixed bottom-0 left-0 right-0 z-50 bg-surface-raised rounded-t-2xl p-6 max-h-[80vh] overflow-y-auto"
            x-data
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
        >
            {{-- Drag handle --}}
            <div class="w-10 h-1 bg-text-secondary/40 rounded-full mx-auto mb-4"></div>

            <h3 class="text-lg font-semibold mb-4">{{ __('app.manual_entry_title') }}</h3>

            <div class="space-y-4">
                <div>
                    <input
                        type="text"
                        wire:model="description"
                        placeholder="{{ __('app.timer_description_placeholder') }}"
                        class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                </div>

                <div>
                    <select
                        wire:model="vectorId"
                        class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="">{{ __('app.timer_no_vector') }}</option>
                        @foreach($vectors as $vector)
                            <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.manual_entry_start') }}</label>
                    <input
                        type="datetime-local"
                        wire:model="startedAt"
                        class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    @error('startedAt')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.manual_entry_end') }}</label>
                    <input
                        type="datetime-local"
                        wire:model="stoppedAt"
                        class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    @error('stoppedAt')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    wire:click="save"
                    class="w-full bg-accent hover:bg-accent-hover text-surface font-semibold py-4 px-8 text-lg rounded-xl [touch-action:manipulation]"
                >
                    {{ __('app.manual_entry_save') }}
                </button>

                <button
                    wire:click="cancel"
                    class="w-full text-text-secondary hover:text-text-primary py-3 text-lg [touch-action:manipulation]"
                >
                    {{ __('app.manual_entry_cancel') }}
                </button>
            </div>
        </div>
    @endif
</div>
