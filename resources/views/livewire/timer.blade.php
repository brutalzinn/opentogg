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
    <div class="bg-surface-raised p-6">
        <div class="text-center mb-6">
            <div class="text-7xl font-mono tabular-nums tracking-tight" x-text="formatted">00:00:00</div>
        </div>

        <div class="space-y-4">
            <input
                type="text"
                wire:model.blur="description"
                wire:change="updateDescription"
                placeholder="{{ __('app.timer_description_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >

            <select
                wire:model.live="vectorId"
                class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="">{{ __('app.timer_no_vector') }}</option>
                @foreach($vectors as $vector)
                    <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                @endforeach
            </select>

            <button
                x-show="!running"
                wire:click="start"
                class="w-full bg-accent hover:bg-accent-hover text-surface font-semibold py-4 px-8 text-xl"
            >
                {{ __('app.timer_start') }}
            </button>
            <button
                x-show="running"
                wire:click="stop"
                class="w-full bg-danger hover:bg-danger/80 text-surface font-semibold py-4 px-8 text-xl"
            >
                {{ __('app.timer_stop') }}
            </button>
        </div>
    </div>
</div>
