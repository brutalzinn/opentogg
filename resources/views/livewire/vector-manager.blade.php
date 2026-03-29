<div>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.vectors_title') }}</h2>

    <form wire:submit="create" class="bg-surface-raised p-4 mb-6 flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.vector_name') }}</label>
            <input
                type="text"
                wire:model="name"
                placeholder="{{ __('app.vector_name_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>
        <div>
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.vector_color') }}</label>
            <input
                type="color"
                wire:model="color"
                class="w-12 h-12 bg-surface-overlay cursor-pointer border-0"
            >
        </div>
        <button type="submit" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 text-lg">
            {{ __('app.vector_create') }}
        </button>
    </form>

    @foreach($vectors as $vector)
        <div class="bg-surface-raised p-4 mb-2 flex items-center justify-between gap-4">
            @if($editingId === $vector->id)
                <div class="flex-1 flex items-center gap-4">
                    <input
                        type="text"
                        wire:model="editingName"
                        wire:keydown.enter="save"
                        class="flex-1 bg-surface-overlay text-text-primary text-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-accent"
                        autofocus
                    >
                    <input
                        type="color"
                        wire:model="editingColor"
                        class="w-10 h-10 bg-surface-overlay cursor-pointer border-0"
                    >
                    <button wire:click="save" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6">
                        {{ __('app.save') }}
                    </button>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 inline-block rounded-full" style="background-color: {{ $vector->color }}"></span>
                    <span class="text-lg">{{ $vector->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="startEdit({{ $vector->id }})" class="text-text-secondary hover:text-text-primary py-3 px-6">
                        {{ __('app.edit') }}
                    </button>
                    <button wire:click="delete({{ $vector->id }})" wire:confirm="{{ __('app.vector_delete_confirm') }}" class="text-danger hover:text-danger/80 py-3 px-6">
                        {{ __('app.delete') }}
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>
