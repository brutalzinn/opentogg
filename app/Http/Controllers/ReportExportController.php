<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    private function getEntries(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();

        return Auth::user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->whereBetween('started_at', [$start, $end])
            ->with('vector')
            ->orderBy('started_at')
            ->get();
    }

    public function csv(Request $request): StreamedResponse
    {
        $entries = $this->getEntries($request);

        $filename = 'opentogg-report-' . $request->start . '-to-' . $request->end . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('app.export_date'),
                __('app.export_description'),
                __('app.export_vector'),
                __('app.export_start'),
                __('app.export_stop'),
                __('app.export_duration'),
            ]);

            foreach ($entries as $entry) {
                $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
                $h = floor($seconds / 3600);
                $m = floor(($seconds % 3600) / 60);
                $s = $seconds % 60;

                fputcsv($handle, [
                    $entry->started_at->toDateString(),
                    $entry->description ?? '',
                    $entry->vector?->name ?? '',
                    $entry->started_at->format('H:i:s'),
                    $entry->stopped_at->format('H:i:s'),
                    sprintf('%02d:%02d:%02d', $h, $m, $s),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function pdf(Request $request)
    {
        $entries = $this->getEntries($request);

        $totalSeconds = $entries->sum(fn($e) => $e->started_at->diffInSeconds($e->stopped_at));
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        $pdf = Pdf::loadView('exports.report-pdf', [
            'entries' => $entries,
            'startDate' => $request->start,
            'endDate' => $request->end,
            'totalHours' => $totalHours,
            'totalMinutes' => $totalMinutes,
            'userName' => Auth::user()->name,
        ]);

        $filename = 'opentogg-report-' . $request->start . '-to-' . $request->end . '.pdf';

        return $pdf->download($filename);
    }
}
