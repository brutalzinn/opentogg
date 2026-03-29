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

        // Build weeks array (columns) with 7 days each (rows = Mon-Sun)
        $weeks = [];
        $monthLabels = [];
        $cursor = $start->copy();
        $currentWeek = [];
        $lastMonth = null;

        while ($cursor->lte($end)) {
            $dayOfWeek = $cursor->dayOfWeekIso - 1; // 0=Mon, 6=Sun
            $dateStr = $cursor->toDateString();
            $hours = round($dailyHours[$dateStr] ?? 0, 2);

            if ($dayOfWeek === 0 && !empty($currentWeek)) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            // Track month labels at the start of each month
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
                'label' => $cursor->format('M d') . ': ' . ($hours > 0 ? round($hours, 1) . 'h' : __('app.reports_heatmap_no_activity')),
            ];

            $cursor->addDay();
        }

        if (!empty($currentWeek)) {
            $weeks[] = $currentWeek;
        }

        // Find max for color scaling
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

        // Daily hours for filtered period
        $dailyData = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dayKey = $cursor->toDateString();
            $dailyData[$dayKey] = 0;
            $cursor->addDay();
        }

        foreach ($entries as $entry) {
            $dayKey = $entry->started_at->toDateString();
            $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
            if (isset($dailyData[$dayKey])) {
                $dailyData[$dayKey] += $seconds / 3600;
            }
        }

        $dailyChart = [
            'labels' => array_map(fn($d) => Carbon::parse($d)->format('M d'), array_keys($dailyData)),
            'data' => array_map(fn($h) => round($h, 2), array_values($dailyData)),
        ];

        // Per-vector breakdown
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

        $vectorChart = [
            'labels' => array_keys($vectorTotals),
            'data' => array_map(fn($s) => round($s / 3600, 2), array_values($vectorTotals)),
            'colors' => array_values($vectorColors),
        ];

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
        $totalSeconds = $entries->sum(fn($e) => $e->started_at->diffInSeconds($e->stopped_at));
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        $activeDays = collect($dailyData)->filter(fn($h) => $h > 0)->count();
        $avgHoursPerDay = $activeDays > 0 ? round($totalSeconds / 3600 / $activeDays, 1) : 0;

        $heatmap = $this->buildHeatmap();

        return view('livewire.reports', [
            'dailyChart' => $dailyChart,
            'vectorChart' => $vectorChart,
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
