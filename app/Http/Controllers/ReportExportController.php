<?php

namespace App\Http\Controllers;

use App\Support\Format;
use App\Support\Preferences;
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

        // The requested range is in the user's local calendar days.
        $tz = Preferences::current()['timezone'];
        $start = Carbon::parse($request->start, $tz)->startOfDay()->utc();
        $end = Carbon::parse($request->end, $tz)->endOfDay()->utc();

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

        $filename = 'opentogg-report-'.$request->start.'-to-'.$request->end.'.csv';

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
                    Format::date($entry->started_at),
                    $entry->description ?? '',
                    $entry->vector?->name ?? '',
                    Format::time($entry->started_at),
                    Format::time($entry->stopped_at),
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

        $totalSeconds = $entries->sum(fn ($e) => $e->started_at->diffInSeconds($e->stopped_at));
        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);

        $hourlyRate = Preferences::current()['hourly_rate'];
        $earnings = $hourlyRate > 0
            ? Format::money(($totalSeconds / 3600) * $hourlyRate)
            : null;

        $pdf = Pdf::loadView('exports.report-pdf', [
            'entries' => $entries,
            'startDate' => Format::date(Carbon::parse($request->start, Preferences::current()['timezone'])),
            'endDate' => Format::date(Carbon::parse($request->end, Preferences::current()['timezone'])),
            'totalHours' => $totalHours,
            'totalMinutes' => $totalMinutes,
            'earnings' => $earnings,
            'userName' => Auth::user()->name,
        ]);

        $filename = 'opentogg-report-'.$request->start.'-to-'.$request->end.'.pdf';

        return $pdf->download($filename);
    }
}
