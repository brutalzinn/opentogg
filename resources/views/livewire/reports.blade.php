<div>
    {{-- GitHub-style activity heatmap (always last 52 weeks) --}}
    <div class="bg-surface-raised p-4 mb-6 overflow-x-auto"
        x-data="{
            maxHours: @js($heatmap['maxHours']),
            getColor(hours) {
                if (hours === 0) return '#161B22';
                const ratio = hours / this.maxHours;
                if (ratio <= 0.25) return '#3B2667';
                if (ratio <= 0.50) return '#6B3FA0';
                if (ratio <= 0.75) return '#9B6DD7';
                return '#BB86FC';
            }
        }"
    >
        <h3 class="text-lg font-semibold mb-3">{{ __('app.reports_heatmap_title') }}</h3>

        {{-- Month labels --}}
        <div class="flex" style="padding-left: 32px;">
            @php
                $totalWeeks = count($heatmap['weeks']);
                $monthPositions = [];
                foreach ($heatmap['monthLabels'] as $ml) {
                    $monthPositions[] = $ml;
                }
            @endphp
            @foreach($monthPositions as $i => $ml)
                @php
                    $nextWeek = isset($monthPositions[$i + 1]) ? $monthPositions[$i + 1]['weekIndex'] : $totalWeeks;
                    $span = $nextWeek - $ml['weekIndex'];
                @endphp
                <span
                    class="text-text-secondary text-xs"
                    style="width: {{ $span * 14 }}px; min-width: {{ $span * 14 }}px;"
                >{{ $ml['label'] }}</span>
            @endforeach
        </div>

        <div class="flex gap-0">
            {{-- Day-of-week labels --}}
            <div class="flex flex-col shrink-0" style="width: 32px;">
                @foreach(['', 'Mon', '', 'Wed', '', 'Fri', ''] as $label)
                    <div class="text-text-secondary text-xs flex items-center" style="height: 12px; margin-bottom: 2px;">{{ $label }}</div>
                @endforeach
            </div>

            {{-- Weeks grid --}}
            <div class="flex gap-[2px]">
                @foreach($heatmap['weeks'] as $week)
                    <div class="flex flex-col gap-[2px]">
                        {{-- Pad empty days at start of first week --}}
                        @if($loop->first && $week[0]['day'] > 0)
                            @for($i = 0; $i < $week[0]['day']; $i++)
                                <div style="width: 12px; height: 12px;"></div>
                            @endfor
                        @endif

                        @foreach($week as $day)
                            <div
                                style="width: 12px; height: 12px; border-radius: 2px;"
                                :style="{ backgroundColor: getColor({{ $day['hours'] }}) }"
                                title="{{ $day['label'] }}"
                            ></div>
                        @endforeach

                        {{-- Pad empty days at end of last week --}}
                        @if($loop->last)
                            @for($i = count($week) + ($loop->first && $week[0]['day'] > 0 ? $week[0]['day'] : 0); $i < 7; $i++)
                                <div style="width: 12px; height: 12px;"></div>
                            @endfor
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex items-center gap-2 mt-3 justify-end">
            <span class="text-text-secondary text-xs">{{ __('app.reports_heatmap_less') }}</span>
            @foreach(['#161B22', '#3B2667', '#6B3FA0', '#9B6DD7', '#BB86FC'] as $color)
                <div style="width: 12px; height: 12px; border-radius: 2px; background-color: {{ $color }};"></div>
            @endforeach
            <span class="text-text-secondary text-xs">{{ __('app.reports_heatmap_more') }}</span>
        </div>
    </div>

    {{-- Period selector --}}
    <div class="bg-surface-raised p-4 mb-6 flex flex-col md:flex-row flex-wrap md:items-end gap-4">
        <div>
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_period') }}</label>
            <select
                wire:model.live="period"
                class="bg-surface-overlay text-text-primary text-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="week">{{ __('app.reports_this_week') }}</option>
                <option value="month">{{ __('app.reports_this_month') }}</option>
                <option value="year">{{ __('app.reports_this_year') }}</option>
                <option value="custom">{{ __('app.reports_custom') }}</option>
            </select>
        </div>

        <div class="flex items-end gap-2 md:ml-auto">
            <a href="{{ route('reports.export.csv', ['start' => $startDate, 'end' => $endDate]) }}"
               class="bg-surface-overlay hover:bg-surface-overlay/80 text-text-primary py-3 px-6 text-sm">
                {{ __('app.export_csv') }}
            </a>
            <a href="{{ route('reports.export.pdf', ['start' => $startDate, 'end' => $endDate]) }}"
               class="bg-surface-overlay hover:bg-surface-overlay/80 text-text-primary py-3 px-6 text-sm">
                {{ __('app.export_pdf') }}
            </a>
        </div>

        @if($period === 'custom')
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_from') }}</label>
                <input
                    type="date"
                    wire:model.live="startDate"
                    class="bg-surface-overlay text-text-primary text-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-accent"
                >
            </div>
            <div>
                <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_to') }}</label>
                <input
                    type="date"
                    wire:model.live="endDate"
                    class="bg-surface-overlay text-text-primary text-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-accent"
                >
            </div>
        @endif
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-surface-raised p-4 text-center">
            <div class="text-3xl font-mono tabular-nums text-accent">{{ $totalHours }}h {{ $totalMinutes }}m</div>
            <div class="text-text-secondary text-sm mt-1">{{ __('app.reports_total_time') }}</div>
        </div>
        <div class="bg-surface-raised p-4 text-center">
            <div class="text-3xl font-mono tabular-nums text-accent">{{ $totalEntries }}</div>
            <div class="text-text-secondary text-sm mt-1">{{ __('app.reports_total_entries') }}</div>
        </div>
        <div class="bg-surface-raised p-4 text-center">
            <div class="text-3xl font-mono tabular-nums text-accent">{{ $activeDays }}</div>
            <div class="text-text-secondary text-sm mt-1">{{ __('app.reports_active_days') }}</div>
        </div>
        <div class="bg-surface-raised p-4 text-center">
            <div class="text-3xl font-mono tabular-nums text-accent">{{ $avgHoursPerDay }}h</div>
            <div class="text-text-secondary text-sm mt-1">{{ __('app.reports_avg_per_day') }}</div>
        </div>
    </div>

    {{-- Daily hours bar chart --}}
    <div class="bg-surface-raised p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">{{ __('app.reports_daily_hours') }}</h3>
        <div
            x-data="{
                chart: null,
                init() {
                    this.render(@js($dailyChart));
                    $wire.on('reports-updated', () => {
                        this.$nextTick(() => this.render(@js($dailyChart)));
                    });
                },
                render(data) {
                    if (this.chart) this.chart.destroy();
                    this.chart = new Chart(this.$refs.dailyCanvas, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: '#BB86FC',
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: {
                                    ticks: { color: '#A0A0A0' },
                                    grid: { color: '#2C2C2C' }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#A0A0A0',
                                        callback: v => v + 'h'
                                    },
                                    grid: { color: '#2C2C2C' }
                                }
                            }
                        }
                    });
                }
            }"
            wire:ignore
        >
            <div style="height: 300px">
                <canvas x-ref="dailyCanvas"></canvas>
            </div>
        </div>
    </div>

    {{-- Vector breakdown doughnut --}}
    @if(count($vectorChart['labels']) > 0)
        <div class="bg-surface-raised p-4 mb-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('app.reports_by_vector') }}</h3>
            <div
                x-data="{
                    chart: null,
                    init() {
                        this.render(@js($vectorChart));
                        $wire.on('reports-updated', () => {
                            this.$nextTick(() => this.render(@js($vectorChart)));
                        });
                    },
                    render(data) {
                        if (this.chart) this.chart.destroy();
                        this.chart = new Chart(this.$refs.vectorCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    data: data.data,
                                    backgroundColor: data.colors,
                                    borderWidth: 0,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: '#E0E0E0',
                                            padding: 16,
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: ctx => ctx.label + ': ' + ctx.parsed + 'h'
                                        }
                                    }
                                }
                            }
                        });
                    }
                }"
                wire:ignore
            >
                <div style="height: 350px">
                    <canvas x-ref="vectorCanvas"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- Top tasks table --}}
    @if(count($topDescriptions) > 0)
        <div class="bg-surface-raised p-4 mb-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('app.reports_top_tasks') }}</h3>
            @foreach($topDescriptions as $desc => $seconds)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-surface-overlay' : '' }}">
                    <span class="text-text-primary truncate flex-1 mr-4">{{ $desc }}</span>
                    <span class="text-text-secondary font-mono tabular-nums shrink-0">
                        @php
                            $h = floor($seconds / 3600);
                            $m = floor(($seconds % 3600) / 60);
                        @endphp
                        {{ $h }}h {{ $m }}m
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
