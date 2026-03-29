<div>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.tags_title') }}</h2>

    <form wire:submit="create" class="bg-surface-raised p-4 mb-6 flex items-end gap-4">
        <div class="flex-1">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.tag_name') }}</label>
            <input
                type="text"
                wire:model="name"
                placeholder="{{ __('app.tag_name_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>
        <button type="submit" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 text-lg">
            {{ __('app.tag_create') }}
        </button>
    </form>

    @foreach($tags as $tag)
        <div class="bg-surface-raised p-4 mb-2 flex items-center justify-between gap-4">
            @if($editingId === $tag->id)
                <div class="flex-1 flex items-center gap-4">
                    <input
                        type="text"
                        wire:model="editingName"
                        wire:keydown.enter="save"
                        class="flex-1 bg-surface-overlay text-text-primary text-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-accent"
                        autofocus
                    >
                    <button wire:click="save" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6">
                        {{ __('app.save') }}
                    </button>
                </div>
            @else
                <span class="text-lg">{{ $tag->name }}</span>
                <div class="flex items-center gap-2">
                    <button wire:click="startEdit({{ $tag->id }})" class="text-text-secondary hover:text-text-primary py-3 px-6">
                        {{ __('app.edit') }}
                    </button>
                    <button wire:click="delete({{ $tag->id }})" wire:confirm="{{ __('app.tag_delete_confirm') }}" class="text-danger hover:text-danger/80 py-3 px-6">
                        {{ __('app.delete') }}
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>
