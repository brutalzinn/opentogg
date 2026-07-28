@props([
    'model',              // Livewire property name bound to this input, e.g. 'description'
    'tags' => [],         // array of existing tag names for autocomplete suggestions
    'placeholder' => '',
    'wireChange' => null, // optional Livewire method to call on change (e.g. 'updateDescription')
    'inputClass' => 'w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent',
])
<div
    x-data="{
        allTags: @js(collect($tags)->values()),
        showSuggestions: false,
        suggestions: [],
        selectedIndex: 0,
        parsedTags: [],
        init() {
            this.extractTags($wire.get('{{ $model }}') || '');
            $wire.$watch('{{ $model }}', (val) => this.extractTags(val || ''));
        },
        extractTags(val) {
            const matches = val.match(/#([\w-]+)/g) || [];
            this.parsedTags = matches.map(m => m.substring(1));
        },
        onInput(e) {
            const val = e.target.value;
            this.extractTags(val);
            const cursor = e.target.selectionStart;
            const before = val.substring(0, cursor);
            const match = before.match(/#([\w-]*)$/);
            if (match) {
                const query = match[1].toLowerCase();
                this.suggestions = this.allTags.filter(t =>
                    t.toLowerCase().startsWith(query) &&
                    !this.parsedTags.map(p => p.toLowerCase()).includes(t.toLowerCase())
                ).slice(0, 8);
                this.showSuggestions = this.suggestions.length > 0;
                this.selectedIndex = 0;
            } else {
                this.showSuggestions = false;
            }
        },
        pick(tag) {
            const input = this.$refs.descInput;
            const val = input.value;
            const cursor = input.selectionStart;
            const before = val.substring(0, cursor);
            const after = val.substring(cursor);
            const replaced = before.replace(/#[\w-]*$/, '#' + tag + ' ');
            input.value = replaced + after;
            $wire.set('{{ $model }}', input.value);
            this.extractTags(input.value);
            this.showSuggestions = false;
            input.focus();
            input.selectionStart = input.selectionEnd = replaced.length;
        },
        onKeydown(e) {
            if (!this.showSuggestions) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                if (this.suggestions.length > 0) {
                    e.preventDefault();
                    this.pick(this.suggestions[this.selectedIndex]);
                }
            } else if (e.key === 'Escape') {
                this.showSuggestions = false;
            }
        }
    }"
    class="relative"
>
    <input
        type="text"
        x-ref="descInput"
        wire:model.blur="{{ $model }}"
        @if($wireChange) wire:change="{{ $wireChange }}" @endif
        @input="onInput($event)"
        @keydown="onKeydown($event)"
        @click.away="showSuggestions = false"
        placeholder="{{ $placeholder }}"
        class="{{ $inputClass }}"
    >
    <div x-show="parsedTags.length > 0" class="flex flex-wrap gap-1 mt-1.5">
        <template x-for="tag in parsedTags" :key="tag">
            <span class="text-xs bg-accent/20 text-accent font-semibold px-2 py-0.5 rounded-full" x-text="'#' + tag"></span>
        </template>
    </div>
    <div
        x-show="showSuggestions"
        x-cloak
        class="absolute z-50 left-0 right-0 mt-1 bg-surface-overlay border border-surface-raised rounded-lg shadow-lg overflow-hidden"
    >
        <template x-for="(tag, i) in suggestions" :key="tag">
            <button
                type="button"
                @click="pick(tag)"
                @mouseenter="selectedIndex = i"
                :class="i === selectedIndex ? 'bg-accent/20 text-accent' : 'text-text-primary'"
                class="w-full text-left px-4 py-2 text-sm hover:bg-accent/10 cursor-pointer"
            >
                <span class="text-accent">#</span><span x-text="tag"></span>
            </button>
        </template>
    </div>
</div>
