<div>
    {{-- Period selector --}}
    <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl space-y-3 sm:space-y-0 sm:flex sm:flex-wrap sm:items-end sm:gap-4">
        <div class="flex-1 min-w-0">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_period') }}</label>
            <select
                wire:model.live="period"
                class="w-full sm:w-auto bg-surface-overlay text-text-primary py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="today">{{ __('app.reports_today') }}</option>
                <option value="week">{{ __('app.reports_this_week') }}</option>
                <option value="month">{{ __('app.reports_this_month') }}</option>
                <option value="year">{{ __('app.reports_this_year') }}</option>
                <option value="custom">{{ __('app.reports_custom') }}</option>
            </select>
        </div>

        @if($period === 'custom')
            <div class="grid grid-cols-2 gap-2 sm:contents">
                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_from') }}</label>
                    <input
                        type="date"
                        wire:model.live="startDate"
                        class="w-full bg-surface-overlay text-text-primary py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                </div>
                <div>
                    <label class="block text-text-secondary text-sm mb-1">{{ __('app.reports_to') }}</label>
                    <input
                        type="date"
                        wire:model.live="endDate"
                        class="w-full bg-surface-overlay text-text-primary py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                </div>
            </div>
        @endif

        <div class="flex gap-2 sm:ml-auto">
            <a href="{{ route('reports.export.csv', ['start' => $startDate, 'end' => $endDate]) }}"
               class="flex-1 sm:flex-none text-center bg-surface-overlay hover:bg-surface-overlay/80 text-text-primary py-2.5 px-4 rounded-lg text-sm [touch-action:manipulation]">
                {{ __('app.export_csv') }}
            </a>
            <a href="{{ route('reports.export.pdf', ['start' => $startDate, 'end' => $endDate]) }}"
               class="flex-1 sm:flex-none text-center bg-surface-overlay hover:bg-surface-overlay/80 text-text-primary py-2.5 px-4 rounded-lg text-sm [touch-action:manipulation]">
                {{ __('app.export_pdf') }}
            </a>
        </div>
    </div>

    {{-- GitHub-style activity heatmap (fully responsive) --}}
    <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl"
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
        <h3 class="text-base sm:text-lg font-semibold mb-3">{{ __('app.reports_heatmap_title') }}</h3>

        @php
            $totalWeeks = count($heatmap['weeks']);
            $monthPositions = $heatmap['monthLabels'];
        @endphp

        {{-- Fully responsive grid — cells scale to fit viewport --}}
        <div style="--gap: 2px; --label-w: 20px;">
            <div class="w-full">
                {{-- Month labels --}}
                <div class="flex text-text-secondary text-[10px] sm:text-xs" style="padding-left: var(--label-w);">
                    @foreach($monthPositions as $i => $ml)
                        @php
                            $nextWeek = isset($monthPositions[$i + 1]) ? $monthPositions[$i + 1]['weekIndex'] : $totalWeeks;
                            $span = $nextWeek - $ml['weekIndex'];
                            $pct = ($span / $totalWeeks) * 100;
                        @endphp
                        <span style="width: {{ $pct }}%; min-width: 0;">{{ $ml['label'] }}</span>
                    @endforeach
                </div>

                <div class="flex gap-0">
                    {{-- Day-of-week labels --}}
                    <div class="flex flex-col shrink-0" style="width: var(--label-w);">
                        @foreach(['', 'M', '', 'W', '', 'F', ''] as $label)
                            <div class="text-text-secondary text-[8px] sm:text-xs flex items-center justify-center aspect-square" style="margin-bottom: var(--gap);">{{ $label }}</div>
                        @endforeach
                    </div>

                    {{-- Weeks grid — uses 1fr columns so cells auto-size to available width --}}
                    <div class="flex-1 grid" style="grid-template-columns: repeat({{ $totalWeeks }}, 1fr); gap: var(--gap);">
                        @foreach($heatmap['weeks'] as $week)
                            <div class="flex flex-col" style="gap: var(--gap);">
                                @if($loop->first && $week[0]['day'] > 0)
                                    @for($i = 0; $i < $week[0]['day']; $i++)
                                        <div class="aspect-square"></div>
                                    @endfor
                                @endif

                                @foreach($week as $day)
                                    <div
                                        class="rounded-[2px] aspect-square"
                                        :style="{ backgroundColor: getColor({{ $day['hours'] }}) }"
                                        title="{{ $day['label'] }}"
                                    ></div>
                                @endforeach

                                @if($loop->last)
                                    @for($i = count($week) + ($loop->first && $week[0]['day'] > 0 ? $week[0]['day'] : 0); $i < 7; $i++)
                                        <div class="aspect-square"></div>
                                    @endfor
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 sm:gap-2 mt-2 justify-end">
            <span class="text-text-secondary text-[10px] sm:text-xs">{{ __('app.reports_heatmap_less') }}</span>
            @foreach(['#161B22', '#3B2667', '#6B3FA0', '#9B6DD7', '#BB86FC'] as $color)
                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-[2px]" style="background-color: {{ $color }};"></div>
            @endforeach
            <span class="text-text-secondary text-[10px] sm:text-xs">{{ __('app.reports_heatmap_more') }}</span>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-surface-raised p-3 sm:p-4 rounded-xl text-center">
            <div class="text-2xl sm:text-3xl font-mono tabular-nums text-accent">{{ $totalHours }}h {{ $totalMinutes }}m</div>
            <div class="text-text-secondary text-xs sm:text-sm mt-1">{{ __('app.reports_total_time') }}</div>
        </div>
        <div class="bg-surface-raised p-3 sm:p-4 rounded-xl text-center">
            <div class="text-2xl sm:text-3xl font-mono tabular-nums text-accent">{{ $totalEntries }}</div>
            <div class="text-text-secondary text-xs sm:text-sm mt-1">{{ __('app.reports_total_entries') }}</div>
        </div>
        <div class="bg-surface-raised p-3 sm:p-4 rounded-xl text-center">
            <div class="text-2xl sm:text-3xl font-mono tabular-nums text-accent">{{ $activeDays }}</div>
            <div class="text-text-secondary text-xs sm:text-sm mt-1">{{ __('app.reports_active_days') }}</div>
        </div>
        <div class="bg-surface-raised p-3 sm:p-4 rounded-xl text-center">
            <div class="text-2xl sm:text-3xl font-mono tabular-nums text-accent">{{ $avgHoursPerDay }}h</div>
            <div class="text-text-secondary text-xs sm:text-sm mt-1">{{ __('app.reports_avg_per_day') }}</div>
        </div>
    </div>

    {{-- Daily hours stacked bar chart (colored by vector) --}}
    <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
        <h3 class="text-base sm:text-lg font-semibold mb-4">{{ __('app.reports_daily_hours') }}</h3>
        <div
            x-data="{
                chart: null,
                init() {
                    this.render(@js($dailyStackedChart));
                    $wire.on('reports-updated', () => {
                        this.$nextTick(() => this.render(@js($dailyStackedChart)));
                    });
                },
                render(data) {
                    if (this.chart) this.chart.destroy();
                    this.chart = new Chart(this.$refs.dailyCanvas, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: data.datasets.length > 1,
                                    position: 'bottom',
                                    labels: {
                                        color: '#E0E0E0',
                                        padding: 12,
                                        boxWidth: 12,
                                        font: { size: 11 }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(1) + 'h'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: { color: '#A0A0A0', maxRotation: 45, font: { size: 10 } },
                                    grid: { color: '#2C2C2C' }
                                },
                                y: {
                                    stacked: true,
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
            <div class="h-48 sm:h-72">
                <canvas x-ref="dailyCanvas"></canvas>
            </div>
        </div>
    </div>

    {{-- Time by vector: list breakdown --}}
    @if(count($vectorBreakdown) > 0)
        <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
            <h3 class="text-base sm:text-lg font-semibold mb-3">{{ __('app.reports_by_vector') }}</h3>

            @php $grandTotalSeconds = collect($vectorBreakdown)->sum('seconds'); @endphp

            <div class="space-y-2">
                @foreach($vectorBreakdown as $vb)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $vb['color'] }}"></span>
                                <span class="text-sm sm:text-base truncate">{{ $vb['name'] }}</span>
                            </div>
                            <span class="text-text-secondary font-mono tabular-nums text-sm shrink-0 ml-2">
                                {{ $vb['hours'] }}h {{ $vb['minutes'] }}m
                            </span>
                        </div>
                        {{-- Progress bar --}}
                        <div class="h-2 bg-surface-overlay rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full"
                                style="width: {{ $grandTotalSeconds > 0 ? round($vb['seconds'] / $grandTotalSeconds * 100, 1) : 0 }}%; background-color: {{ $vb['color'] }};"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Vector breakdown doughnut --}}
    @if(count($vectorChart['labels']) > 0)
        <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
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
                                            padding: 12,
                                            boxWidth: 12,
                                            font: { size: 11 }
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
                <div class="h-56 sm:h-80">
                    <canvas x-ref="vectorCanvas"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- Top tasks table --}}
    @if(count($topDescriptions) > 0)
        <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
            <h3 class="text-base sm:text-lg font-semibold mb-3">{{ __('app.reports_top_tasks') }}</h3>
            @foreach($topDescriptions as $desc => $seconds)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-surface-overlay' : '' }}">
                    <span class="text-sm sm:text-base text-text-primary truncate flex-1 mr-3">{{ $desc }}</span>
                    <span class="text-text-secondary font-mono tabular-nums text-sm shrink-0">
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
