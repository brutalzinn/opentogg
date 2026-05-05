<div wire:poll.5s>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.time_log_title') }}</h2>

    @forelse($entries as $entry)
        @if($editingEntryId === $entry->id)
            {{-- Inline Edit Form --}}
            <div class="bg-surface-overlay border-l-4 border-accent p-3 sm:p-4 mb-2 rounded-xl space-y-3">
                <input
                    type="text"
                    wire:model="editDescription"
                    placeholder="{{ __('app.timer_description_placeholder') }}"
                    class="w-full bg-surface text-text-primary text-lg py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                    autofocus
                >

                <select
                    wire:model="editVectorId"
                    class="w-full bg-surface text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                >
                    <option value="">{{ __('app.timer_no_vector') }}</option>
                    @foreach($vectors as $vector)
                        <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                    @endforeach
                </select>

                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.entry_edit_start') }}</label>
                    <input
                        type="datetime-local"
                        wire:model="editStartedAt"
                        class="w-full bg-surface text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    @error('editStartedAt')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.entry_edit_end') }}</label>
                    <input
                        type="datetime-local"
                        wire:model="editStoppedAt"
                        class="w-full bg-surface text-text-primary text-lg py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    @error('editStoppedAt')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button
                        wire:click="saveFullEdit"
                        class="flex-1 bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-4 text-lg rounded-xl [touch-action:manipulation]"
                    >
                        {{ __('app.entry_save') }}
                    </button>
                    <button
                        wire:click="cancelFullEdit"
                        class="flex-1 text-text-secondary hover:text-text-primary py-3 px-4 text-lg rounded-xl border border-surface-overlay [touch-action:manipulation]"
                    >
                        {{ __('app.entry_cancel') }}
                    </button>
                </div>
            </div>
        @else
            {{-- Entry Display --}}
            <div class="bg-surface-raised p-3 sm:p-4 mb-2 rounded-xl flex items-center justify-between gap-3">
                <div
                    class="flex-1 min-w-0 cursor-pointer"
                    wire:click="startFullEdit({{ $entry->id }})"
                >
                    <div class="text-lg truncate py-1 px-1">
                        {{ $entry->description ?: __('app.time_log_no_description') }}
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 mt-1 px-1">
                        @if($entry->vector)
                            <span class="text-sm" style="color: {{ $entry->vector->color }}">{{ $entry->vector->name }}</span>
                        @endif
                        <span class="text-text-secondary text-sm" x-data x-text="
                            new Date('{{ $entry->started_at->toISOString() }}').toLocaleTimeString(navigator.language, {hour: '2-digit', minute: '2-digit'})
                            + ' – ' +
                            new Date('{{ $entry->stopped_at->toISOString() }}').toLocaleTimeString(navigator.language, {hour: '2-digit', minute: '2-digit'})
                        ">
                            {{ $entry->started_at->format('H:i') }} – {{ $entry->stopped_at->format('H:i') }}
                        </span>
                        <span class="text-text-secondary text-sm">
                            @php
                                $diff = $entry->started_at->diff($entry->stopped_at);
                                echo sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                            @endphp
                        </span>
                    </div>
                </div>

                <button
                    wire:click="delete({{ $entry->id }})"
                    wire:confirm="{{ __('app.entry_delete_confirm') }}"
                    class="text-danger hover:text-danger/80 p-3 shrink-0 [touch-action:manipulation]"
                    aria-label="{{ __('app.entry_delete') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </button>
            </div>
        @endif
    @empty
        <p class="text-text-secondary text-center py-8">{{ __('app.time_log_empty') }}</p>
    @endforelse
</div>
