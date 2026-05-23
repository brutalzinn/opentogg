<div
    x-data="{
        running: $wire.entangle('isRunning'),
        startedAt: @js($startedAtUnix),
        elapsed: 0,
        interval: null,
        init() {
            if (this.running && this.startedAt) {
                this.elapsed = Math.floor(Date.now() / 1000) - this.startedAt;
                this.startInterval();
            }
            $wire.on('timer-started', (data) => {
                const ts = typeof data === 'object' && data !== null
                    ? (data.startedAt ?? data[0]?.startedAt ?? data)
                    : data;
                this.startedAt = ts;
                this.elapsed = 0;
                this.startInterval();
            });
            $wire.on('timer-stopped', () => {
                this.stopInterval();
                this.elapsed = 0;
                this.startedAt = null;
            });
        },
        startInterval() {
            this.stopInterval();
            this.interval = setInterval(() => {
                this.elapsed = Math.floor(Date.now() / 1000) - this.startedAt;
            }, 1000);
        },
        stopInterval() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },
        get formatted() {
            const h = Math.floor(this.elapsed / 3600);
            const m = Math.floor((this.elapsed % 3600) / 60);
            const s = this.elapsed % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
    }"
>
    <div class="bg-surface-raised p-4 sm:p-6 rounded-2xl" wire:poll.5s="syncState">
        <div class="text-center mb-4 sm:mb-6">
            <div
                class="text-5xl sm:text-7xl font-mono tabular-nums tracking-tight"
                :class="running ? 'animate-pulse-glow' : ''"
                x-text="formatted"
            >00:00:00</div>
        </div>

        <div class="space-y-3">
            <div
                x-data="{
                    allTags: @js($tags),
                    showSuggestions: false,
                    suggestions: [],
                    selectedIndex: 0,
                    parsedTags: [],
                    init() {
                        this.extractTags($wire.get('description') || '');
                        $wire.$watch('description', (val) => this.extractTags(val || ''));
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
                        $wire.set('description', input.value);
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
                    wire:model.blur="description"
                    wire:change="updateDescription"
                    @input="onInput($event)"
                    @keydown="onKeydown($event)"
                    @click.away="showSuggestions = false"
                    placeholder="{{ __('app.timer_description_placeholder_tags') }}"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
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

            <select
                wire:model.live="vectorId"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="">{{ __('app.timer_no_vector') }}</option>
                @foreach($vectors as $vector)
                    <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                @endforeach
            </select>

            <button
                x-show="!running"
                wire:click="start"
                class="w-full bg-accent hover:bg-accent-hover text-surface font-semibold py-4 px-8 text-xl rounded-xl [touch-action:manipulation]"
            >
                {{ __('app.timer_start') }}
            </button>
            <button
                x-show="running"
                wire:click="stop"
                class="w-full bg-danger hover:bg-danger/80 text-surface font-semibold py-4 px-8 text-xl rounded-xl [touch-action:manipulation]"
            >
                {{ __('app.timer_stop') }}
            </button>
        </div>
    </div>
</div>
