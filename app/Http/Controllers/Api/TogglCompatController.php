<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TogglCompatController extends Controller
{
    /**
     * GET /api/v9/me — Current user info (Toggl format).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'fullname' => $user->name,
            'default_workspace_id' => $user->vectors()->first()?->id ?? 0,
            'beginning_of_week' => 1,
            'image_url' => $user->avatar,
        ]);
    }

    /**
     * GET /api/v9/me/workspaces — List workspaces (maps to vectors).
     */
    public function workspaces(Request $request): JsonResponse
    {
        $vectors = $request->user()->vectors()->orderBy('name')->get();

        $workspaces = $vectors->map(fn ($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'admin' => true,
            'premium' => false,
        ]);

        return response()->json($workspaces->values());
    }

    /**
     * GET /api/v9/me/time_entries/current — Currently running time entry.
     * Returns null (not 404) when no timer is running (Toggl convention).
     */
    public function currentEntry(Request $request): JsonResponse|Response
    {
        $entry = $request->user()->timeEntries()
            ->whereNull('stopped_at')
            ->with('vector')
            ->first();

        if (!$entry) {
            return response()->make('null', 200, ['Content-Type' => 'application/json']);
        }

        return response()->json($this->formatEntry($entry));
    }

    /**
     * GET /api/v9/me/time_entries — List recent time entries.
     */
    public function listEntries(Request $request): JsonResponse
    {
        $entries = $request->user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->with('vector')
            ->orderByDesc('started_at')
            ->limit(50)
            ->get();

        return response()->json($entries->map(fn ($e) => $this->formatEntry($e))->values());
    }

    /**
     * POST /api/v9/workspaces/{wid}/time_entries — Start a new timer.
     * Accepts duration: -1 (Toggl convention for running timer).
     */
    public function startEntry(Request $request, int $wid): JsonResponse
    {
        $user = $request->user();

        // Stop any running entry
        $running = $user->timeEntries()->whereNull('stopped_at')->first();
        if ($running) {
            $running->stopped_at = now();
            $running->save();
        }

        // Resolve workspace (vector) — wid is internal id
        $vector = $user->vectors()->find($wid);

        // Also accept project_id (pid) to set the vector
        $pid = $request->input('project_id') ?? $request->input('pid');
        if ($pid && !$vector) {
            $vector = $user->vectors()->find($pid);
        }

        $entry = $user->timeEntries()->create([
            'description' => $request->input('description'),
            'vector_id' => $vector?->id,
            'started_at' => now(),
        ]);

        $entry->load('vector');

        return response()->json($this->formatEntry($entry), 200);
    }

    /**
     * PATCH /api/v9/workspaces/{wid}/time_entries/{id}/stop — Stop a running timer.
     */
    public function stopEntry(Request $request, int $wid, int $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()->find($id);

        if (!$entry || $entry->stopped_at) {
            return response()->json(['error' => 'No running timer found'], 404);
        }

        $entry->stopped_at = now();
        $entry->save();
        $entry->load('vector');

        return response()->json($this->formatEntry($entry));
    }

    /**
     * PUT /api/v9/workspaces/{wid}/time_entries/{id} — Update a time entry.
     */
    public function updateEntry(Request $request, int $wid, int $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()->find($id);

        if (!$entry) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($request->has('description')) {
            $entry->description = $request->input('description');
        }

        $pid = $request->input('project_id') ?? $request->input('pid');
        if ($pid !== null) {
            $vector = $request->user()->vectors()->find($pid);
            $entry->vector_id = $vector?->id;
        }

        $entry->save();
        $entry->load('vector');

        return response()->json($this->formatEntry($entry));
    }

    /**
     * DELETE /api/v9/workspaces/{wid}/time_entries/{id} — Delete a time entry.
     */
    public function deleteEntry(Request $request, int $wid, int $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()->find($id);

        if (!$entry) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $entry->delete();

        return response()->json(null, 200);
    }

    /**
     * GET /api/v9/workspaces/{wid}/projects — List projects (maps to vectors within workspace).
     * Since we map workspaces to vectors, projects are also vectors.
     */
    public function projects(Request $request, int $wid): JsonResponse
    {
        $vectors = $request->user()->vectors()->orderBy('name')->get();

        $projects = $vectors->map(fn ($v) => [
            'id' => $v->id,
            'wid' => $wid,
            'name' => $v->name,
            'color' => $v->color,
            'active' => true,
        ]);

        return response()->json($projects->values());
    }

    /**
     * Format a TimeEntry into Toggl-compatible JSON.
     */
    private function formatEntry($entry): array
    {
        $isRunning = $entry->stopped_at === null;

        return [
            'id' => $entry->id,
            'workspace_id' => $entry->vector_id ?? 0,
            'wid' => $entry->vector_id ?? 0,
            'project_id' => $entry->vector_id,
            'pid' => $entry->vector_id,
            'description' => $entry->description ?? '',
            'start' => $entry->started_at->toIso8601String(),
            'stop' => $entry->stopped_at?->toIso8601String(),
            'duration' => $isRunning
                ? -1 * $entry->started_at->timestamp
                : $entry->started_at->diffInSeconds($entry->stopped_at),
            'tags' => [],
            'at' => $entry->updateAt->toIso8601String(),
        ];
    }
}
