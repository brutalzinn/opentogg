<?php

namespace App\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reports extends Component
{
    public string $period = 'week';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    private function applyPeriod(): void
    {
        $now = now();

        match ($this->period) {
            'week' => $this->setRange($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'month' => $this->setRange($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
            'year' => $this->setRange($now->copy()->startOfYear(), $now->copy()->endOfYear()),
            default => null,
        };
    }

    private function setRange(Carbon $start, Carbon $end): void
    {
        $this->startDate = $start->toDateString();
        $this->endDate = $end->toDateString();
    }

    private function buildHeatmap(): array
    {
        $end = now()->endOfDay();
        $start = $end->copy()->subWeeks(52)->startOfWeek();

        $entries = Auth::user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->where('started_at', '>=', $start)
            ->get();

        $dailyHours = [];
        foreach ($entries as $entry) {
            $dayKey = $entry->started_at->toDateString();
            $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
            $dailyHours[$dayKey] = ($dailyHours[$dayKey] ?? 0) + $seconds / 3600;
        }

        $weeks = [];
        $monthLabels = [];
        $cursor = $start->copy();
        $currentWeek = [];
        $lastMonth = null;

        while ($cursor->lte($end)) {
            $dayOfWeek = $cursor->dayOfWeekIso - 1;
            $dateStr = $cursor->toDateString();
            $hours = round($dailyHours[$dateStr] ?? 0, 2);

            if ($dayOfWeek === 0 && ! empty($currentWeek)) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            $month = $cursor->month;
            if ($month !== $lastMonth) {
                $monthLabels[] = [
                    'weekIndex' => count($weeks),
                    'label' => $cursor->format('M'),
                ];
                $lastMonth = $month;
            }

            $currentWeek[] = [
                'date' => $dateStr,
                'hours' => $hours,
                'day' => $dayOfWeek,
                'label' => $cursor->format('M d').': '.($hours > 0 ? round($hours, 1).'h' : __('app.reports_heatmap_no_activity')),
            ];

            $cursor->addDay();
        }

        if (! empty($currentWeek)) {
            $weeks[] = $currentWeek;
        }

        $maxHours = max(1, max(array_values($dailyHours) ?: [0]));

        return [
            'weeks' => $weeks,
            'monthLabels' => $monthLabels,
            'maxHours' => round($maxHours, 1),
        ];
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $entries = Auth::user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->whereBetween('started_at', [$start, $end])
            ->with('vector')
            ->get();

        // Build daily data keyed by date
        $dailyDates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dailyDates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        // Collect all vectors used in period + "No vector"
        $vectorMap = []; // vectorKey => [name, color]
        $dailyByVector = []; // vectorKey => [date => hours]

        foreach ($entries as $entry) {
            $dayKey = $entry->started_at->toDateString();
            $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
            $hours = $seconds / 3600;

            if ($entry->vector) {
                $vKey = 'v_'.$entry->vector->id;
                $vectorMap[$vKey] = ['name' => $entry->vector->name, 'color' => $entry->vector->color];
            } else {
                $vKey = '_none';
                $vectorMap[$vKey] = ['name' => __('app.reports_no_vector'), 'color' => '#6B7280'];
            }

            if (! isset($dailyByVector[$vKey])) {
                $dailyByVector[$vKey] = [];
            }
            $dailyByVector[$vKey][$dayKey] = ($dailyByVector[$vKey][$dayKey] ?? 0) + $hours;
        }

        // Build stacked datasets for Chart.js
        $datasets = [];
        foreach ($vectorMap as $vKey => $info) {
            $data = [];
            foreach ($dailyDates as $date) {
                $data[] = round($dailyByVector[$vKey][$date] ?? 0, 2);
            }
            $datasets[] = [
                'label' => $info['name'],
                'data' => $data,
                'backgroundColor' => $info['color'],
                'borderRadius' => 4,
            ];
        }

        $dailyStackedChart = [
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('M d'), $dailyDates),
            'datasets' => $datasets,
        ];

        // Per-vector breakdown (for list + doughnut)
        $vectorTotals = [];
        $vectorColors = [];
        $noVectorSeconds = 0;

        foreach ($entries as $entry) {
            $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
            if ($entry->vector) {
                $name = $entry->vector->name;
                $vectorTotals[$name] = ($vectorTotals[$name] ?? 0) + $seconds;
                $vectorColors[$name] = $entry->vector->color;
            } else {
                $noVectorSeconds += $seconds;
            }
        }

        if ($noVectorSeconds > 0) {
            $vectorTotals[__('app.reports_no_vector')] = $noVectorSeconds;
            $vectorColors[__('app.reports_no_vector')] = '#6B7280';
        }

        // Sort by total time descending
        arsort($vectorTotals);

        $vectorChart = [
            'labels' => array_keys($vectorTotals),
            'data' => array_map(fn ($s) => round($s / 3600, 2), array_values($vectorTotals)),
            'colors' => array_map(fn ($name) => $vectorColors[$name], array_keys($vectorTotals)),
        ];

        // Vector breakdown for list display
        $vectorBreakdown = [];
        foreach ($vectorTotals as $name => $seconds) {
            $vectorBreakdown[] = [
                'name' => $name,
                'color' => $vectorColors[$name],
                'seconds' => $seconds,
                'hours' => floor($seconds / 3600),
                'minutes' => floor(($seconds % 3600) / 60),
            ];
        }

        // Top descriptions
        $descriptionTotals = [];
        foreach ($entries as $entry) {
            $desc = $entry->description ?: __('app.time_log_no_description');
            $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
            $descriptionTotals[$desc] = ($descriptionTotals[$desc] ?? 0) + $seconds;
        }
        arsort($descriptionTotals);
        $topDescriptions = array_slice($descriptionTotals, 0, 10, true);

        // Total hours
        $totalSeconds = $entries->sum(fn ($e) => $e->started_at->diffInSeconds($e->stopped_at));
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        $dailyTotals = [];
        foreach ($dailyDates as $date) {
            $total = 0;
            foreach ($dailyByVector as $vData) {
                $total += $vData[$date] ?? 0;
            }
            $dailyTotals[$date] = $total;
        }

        $activeDays = collect($dailyTotals)->filter(fn ($h) => $h > 0)->count();
        $avgHoursPerDay = $activeDays > 0 ? round($totalSeconds / 3600 / $activeDays, 1) : 0;

        $heatmap = $this->buildHeatmap();

        return view('livewire.reports', [
            'dailyStackedChart' => $dailyStackedChart,
            'vectorChart' => $vectorChart,
            'vectorBreakdown' => $vectorBreakdown,
            'topDescriptions' => $topDescriptions,
            'totalHours' => $totalHours,
            'totalMinutes' => $totalMinutes,
            'avgHoursPerDay' => $avgHoursPerDay,
            'totalEntries' => $entries->count(),
            'activeDays' => $activeDays,
            'heatmap' => $heatmap,
        ]);
    }
}
