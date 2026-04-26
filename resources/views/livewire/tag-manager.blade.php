<div>
    <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ __('app.tags_title') }}</h2>

    <form wire:submit="create" class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl flex items-end gap-3">
        <div class="flex-1">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.tag_name') }}</label>
            <input
                type="text"
                wire:model="name"
                placeholder="{{ __('app.tag_name_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>
        <button type="submit" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-5 rounded-xl [touch-action:manipulation]">
            {{ __('app.tag_create') }}
        </button>
    </form>

    @foreach($tags as $tag)
        <div class="bg-surface-raised p-3 sm:p-4 mb-2 rounded-xl">
            @if($editingId === $tag->id)
                <div class="flex items-center gap-3">
                    <input
                        type="text"
                        wire:model="editingName"
                        wire:keydown.enter="save"
                        class="flex-1 bg-surface-overlay text-text-primary py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        autofocus
                    >
                    <button wire:click="save" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-5 rounded-xl [touch-action:manipulation]">
                        {{ __('app.save') }}
                    </button>
                </div>
            @else
                <div class="flex items-center justify-between gap-3">
                    <span class="text-base sm:text-lg truncate">{{ $tag->name }}</span>
                    <div class="flex items-center gap-1 shrink-0">
                        <button wire:click="startEdit({{ $tag->id }})" class="text-text-secondary hover:text-text-primary p-2.5 [touch-action:manipulation]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                        </button>
                        <button wire:click="delete({{ $tag->id }})" wire:confirm="{{ __('app.tag_delete_confirm') }}" class="text-danger hover:text-danger/80 p-2.5 [touch-action:manipulation]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>
