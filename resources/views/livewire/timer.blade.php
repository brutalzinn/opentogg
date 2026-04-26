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
            <input
                type="text"
                wire:model.blur="description"
                wire:change="updateDescription"
                placeholder="{{ __('app.timer_description_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >

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
