<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataPortController extends Controller
{
    public function export(): StreamedResponse
    {
        $user = Auth::user();

        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'vectors' => $user->vectors()->get()->map(fn($v) => [
                'external_id' => $v->external_id,
                'name' => $v->name,
                'color' => $v->color,
                'createAt' => $v->createAt?->toIso8601String(),
            ])->toArray(),
            'tags' => $user->tags()->get()->map(fn($t) => [
                'external_id' => $t->external_id,
                'name' => $t->name,
                'createAt' => $t->createAt?->toIso8601String(),
            ])->toArray(),
            'time_entries' => $user->timeEntries()
                ->with('vector', 'tags')
                ->orderBy('started_at')
                ->get()
                ->map(fn($e) => [
                    'external_id' => $e->external_id,
                    'description' => $e->description,
                    'vector_external_id' => $e->vector?->external_id,
                    'tag_external_ids' => $e->tags->pluck('external_id')->toArray(),
                    'started_at' => $e->started_at->toIso8601String(),
                    'stopped_at' => $e->stopped_at?->toIso8601String(),
                    'createAt' => $e->createAt?->toIso8601String(),
                ])->toArray(),
        ];

        $filename = 'opentogg-export-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['version'])) {
            return back()->with('error', __('app.import_invalid_file'));
        }

        $user = Auth::user();

        DB::transaction(function () use ($data, $user) {
            $vectorMap = [];
            foreach ($data['vectors'] ?? [] as $v) {
                $vector = $user->vectors()->updateOrCreate(
                    ['external_id' => $v['external_id']],
                    [
                        'name' => $v['name'],
                        'color' => $v['color'] ?? '#6B7280',
                    ]
                );
                $vectorMap[$v['external_id']] = $vector->id;
            }

            $tagMap = [];
            foreach ($data['tags'] ?? [] as $t) {
                $tag = $user->tags()->updateOrCreate(
                    ['external_id' => $t['external_id']],
                    ['name' => $t['name']]
                );
                $tagMap[$t['external_id']] = $tag->id;
            }

            foreach ($data['time_entries'] ?? [] as $e) {
                $vectorId = null;
                if (!empty($e['vector_external_id']) && isset($vectorMap[$e['vector_external_id']])) {
                    $vectorId = $vectorMap[$e['vector_external_id']];
                }

                $entry = $user->timeEntries()->updateOrCreate(
                    ['external_id' => $e['external_id']],
                    [
                        'description' => $e['description'],
                        'vector_id' => $vectorId,
                        'started_at' => $e['started_at'],
                        'stopped_at' => $e['stopped_at'] ?? null,
                    ]
                );

                $tagIds = [];
                foreach ($e['tag_external_ids'] ?? [] as $tagExtId) {
                    if (isset($tagMap[$tagExtId])) {
                        $tagIds[] = $tagMap[$tagExtId];
                    }
                }
                $entry->tags()->sync($tagIds);
            }
        });

        return back()->with('success', __('app.import_success'));
    }

    #[OA\Get(
        path: '/export',
        summary: 'Export all user data',
        description: 'Returns all vectors, tags, and time entries as JSON for backup or migration.',
        security: [['bearerAuth' => []]],
        tags: ['Data Portability'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Full data export',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'version', type: 'integer', example: 1),
                        new OA\Property(property: 'exported_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'vectors', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'time_entries', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
        ]
    )]
    public function apiExport(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'vectors' => $user->vectors()->get()->map(fn($v) => [
                'external_id' => $v->external_id,
                'name' => $v->name,
                'color' => $v->color,
            ])->toArray(),
            'tags' => $user->tags()->get()->map(fn($t) => [
                'external_id' => $t->external_id,
                'name' => $t->name,
            ])->toArray(),
            'time_entries' => $user->timeEntries()
                ->with('vector', 'tags')
                ->orderBy('started_at')
                ->get()
                ->map(fn($e) => [
                    'external_id' => $e->external_id,
                    'description' => $e->description,
                    'vector_external_id' => $e->vector?->external_id,
                    'tag_external_ids' => $e->tags->pluck('external_id')->toArray(),
                    'started_at' => $e->started_at->toIso8601String(),
                    'stopped_at' => $e->stopped_at?->toIso8601String(),
                ])->toArray(),
        ];

        return response()->json($data);
    }

    #[OA\Post(
        path: '/import',
        summary: 'Import user data',
        description: 'Import vectors, tags, and time entries. Uses external_id for upsert — safe for syncing between instances.',
        security: [['bearerAuth' => []]],
        tags: ['Data Portability'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['version'],
                properties: [
                    new OA\Property(property: 'version', type: 'integer', example: 1),
                    new OA\Property(
                        property: 'vectors',
                        type: 'array',
                        items: new OA\Items(
                            required: ['external_id', 'name'],
                            properties: [
                                new OA\Property(property: 'external_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'color', type: 'string', example: '#6B7280'),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(
                            required: ['external_id', 'name'],
                            properties: [
                                new OA\Property(property: 'external_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'name', type: 'string'),
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'time_entries',
                        type: 'array',
                        items: new OA\Items(
                            required: ['external_id', 'started_at'],
                            properties: [
                                new OA\Property(property: 'external_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'vector_external_id', type: 'string', nullable: true),
                                new OA\Property(property: 'tag_external_ids', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'started_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'stopped_at', type: 'string', format: 'date-time', nullable: true),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Import complete',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'imported',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'vectors', type: 'integer'),
                                new OA\Property(property: 'tags', type: 'integer'),
                                new OA\Property(property: 'time_entries', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function apiImport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'version' => 'required|integer',
            'vectors' => 'nullable|array',
            'vectors.*.external_id' => 'required|string',
            'vectors.*.name' => 'required|string',
            'vectors.*.color' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.external_id' => 'required|string',
            'tags.*.name' => 'required|string',
            'time_entries' => 'nullable|array',
            'time_entries.*.external_id' => 'required|string',
            'time_entries.*.started_at' => 'required|date',
            'time_entries.*.stopped_at' => 'nullable|date',
            'time_entries.*.description' => 'nullable|string',
            'time_entries.*.vector_external_id' => 'nullable|string',
            'time_entries.*.tag_external_ids' => 'nullable|array',
        ]);

        $user = $request->user();
        $counts = ['vectors' => 0, 'tags' => 0, 'time_entries' => 0];

        DB::transaction(function () use ($data, $user, &$counts) {
            $vectorMap = [];
            foreach ($data['vectors'] ?? [] as $v) {
                $vector = $user->vectors()->updateOrCreate(
                    ['external_id' => $v['external_id']],
                    ['name' => $v['name'], 'color' => $v['color'] ?? '#6B7280']
                );
                $vectorMap[$v['external_id']] = $vector->id;
                $counts['vectors']++;
            }

            $tagMap = [];
            foreach ($data['tags'] ?? [] as $t) {
                $tag = $user->tags()->updateOrCreate(
                    ['external_id' => $t['external_id']],
                    ['name' => $t['name']]
                );
                $tagMap[$t['external_id']] = $tag->id;
                $counts['tags']++;
            }

            foreach ($data['time_entries'] ?? [] as $e) {
                $vectorId = null;
                if (!empty($e['vector_external_id']) && isset($vectorMap[$e['vector_external_id']])) {
                    $vectorId = $vectorMap[$e['vector_external_id']];
                }

                $entry = $user->timeEntries()->updateOrCreate(
                    ['external_id' => $e['external_id']],
                    [
                        'description' => $e['description'] ?? null,
                        'vector_id' => $vectorId,
                        'started_at' => $e['started_at'],
                        'stopped_at' => $e['stopped_at'] ?? null,
                    ]
                );

                $tagIds = [];
                foreach ($e['tag_external_ids'] ?? [] as $tagExtId) {
                    if (isset($tagMap[$tagExtId])) {
                        $tagIds[] = $tagMap[$tagExtId];
                    }
                }
                $entry->tags()->sync($tagIds);
                $counts['time_entries']++;
            }
        });

        return response()->json([
            'message' => 'Import complete.',
            'imported' => $counts,
        ]);
    }
}
