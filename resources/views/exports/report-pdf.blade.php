<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #E0E0E0; background: #121212; font-size: 12px; }
        h1 { color: #BB86FC; font-size: 20px; margin-bottom: 4px; }
        .meta { color: #A0A0A0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #A0A0A0; padding: 8px 6px; border-bottom: 1px solid #2C2C2C; }
        td { padding: 6px; border-bottom: 1px solid #1E1E1E; }
        .total { margin-top: 16px; text-align: right; font-size: 14px; color: #BB86FC; }
        .vector-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }} — {{ __('app.reports_pdf_title') }}</h1>
    <div class="meta">
        {{ $userName }} &middot; {{ $startDate }} {{ __('app.reports_to') }} {{ $endDate }}
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.export_date') }}</th>
                <th>{{ __('app.export_description') }}</th>
                <th>{{ __('app.export_vector') }}</th>
                <th>{{ __('app.export_start') }}</th>
                <th>{{ __('app.export_stop') }}</th>
                <th>{{ __('app.export_duration') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
                @php
                    $seconds = $entry->started_at->diffInSeconds($entry->stopped_at);
                    $h = floor($seconds / 3600);
                    $m = floor(($seconds % 3600) / 60);
                    $s = $seconds % 60;
                @endphp
                <tr>
                    <td>{{ $entry->started_at->format('Y-m-d') }}</td>
                    <td>{{ $entry->description ?? '-' }}</td>
                    <td>
                        @if($entry->vector)
                            <span class="vector-dot" style="background-color: {{ $entry->vector->color }}"></span>
                            {{ $entry->vector->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $entry->started_at->format('H:i') }}</td>
                    <td>{{ $entry->stopped_at->format('H:i') }}</td>
                    <td>{{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        {{ __('app.reports_total_time') }}: {{ $totalHours }}h {{ $totalMinutes }}m
    </div>
</body>
</html>
