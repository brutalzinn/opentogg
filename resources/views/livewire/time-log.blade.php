<div>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.time_log_title') }}</h2>

    @forelse($entries as $entry)
        <div class="bg-surface-raised p-4 mb-2 flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                @if($editingId === $entry->id)
                    <input
                        type="text"
                        wire:model="editingDescription"
                        wire:blur="saveDescription({{ $entry->id }})"
                        wire:keydown.enter="saveDescription({{ $entry->id }})"
                        class="w-full bg-surface-overlay text-text-primary text-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-accent"
                        autofocus
                    >
                @else
                    <button
                        wire:click="startEdit({{ $entry->id }}, '{{ addslashes($entry->description ?? '') }}')"
                        class="text-left w-full py-2 px-3 hover:bg-surface-overlay text-lg truncate"
                    >
                        {{ $entry->description ?: __('app.time_log_no_description') }}
                    </button>
                @endif

                <div class="flex items-center gap-3 mt-1 px-3">
                    @if($entry->vector)
                        <span class="text-sm" style="color: {{ $entry->vector->color }}">{{ $entry->vector->name }}</span>
                    @endif
                    <span class="text-text-secondary text-sm">
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
                class="text-danger hover:text-danger/80 py-3 px-6 text-sm shrink-0"
            >
                {{ __('app.entry_delete') }}
            </button>
        </div>
    @empty
        <p class="text-text-secondary text-center py-8">{{ __('app.time_log_empty') }}</p>
    @endforelse
</div>
