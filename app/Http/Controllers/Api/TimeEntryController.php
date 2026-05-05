<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TimeEntryController extends Controller
{
    #[OA\Get(
        path: '/time-entries',
        summary: 'List completed time entries',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        parameters: [
            new OA\Parameter(name: 'start', in: 'query', description: 'Filter from date (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'end', in: 'query', description: 'Filter to date (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (1-100, default 50)', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated time entries'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->user()->timeEntries()
            ->whereNotNull('stopped_at')
            ->with('vector:id,external_id,name,color', 'tags:id,external_id,name')
            ->orderByDesc('started_at');

        if ($request->start) {
            $query->where('started_at', '>=', $request->start);
        }
        if ($request->end) {
            $query->where('started_at', '<=', $request->end);
        }

        return response()->json($query->paginate($request->integer('per_page', 50)));
    }

    #[OA\Get(
        path: '/time-entries/{id}',
        summary: 'Get a time entry',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Time entry external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Time entry details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, string $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()
            ->where('external_id', $id)
            ->with('vector:id,external_id,name,color', 'tags:id,external_id,name')
            ->firstOrFail();

        return response()->json(['data' => $entry]);
    }

    #[OA\Put(
        path: '/time-entries/{id}',
        summary: 'Update a time entry',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Time entry external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'vector_id', type: 'string', format: 'uuid', description: 'Vector external_id', nullable: true),
                    new OA\Property(property: 'tag_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), description: 'Tag external_ids'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Time entry updated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()->where('external_id', $id)->firstOrFail();

        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'vector_id' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'string',
        ]);

        if (array_key_exists('description', $data)) {
            $entry->description = $data['description'];
        }

        if (array_key_exists('vector_id', $data)) {
            if ($data['vector_id']) {
                $vector = $request->user()->vectors()->where('external_id', $data['vector_id'])->first();
                $entry->vector_id = $vector?->id;
            } else {
                $entry->vector_id = null;
            }
        }

        $entry->save();

        if (isset($data['tag_ids'])) {
            $tagIds = $request->user()->tags()
                ->whereIn('external_id', $data['tag_ids'])
                ->pluck('id')
                ->toArray();
            $entry->tags()->sync($tagIds);
        }

        $entry->load('vector:id,external_id,name,color', 'tags:id,external_id,name');

        return response()->json(['data' => $entry]);
    }

    #[OA\Delete(
        path: '/time-entries/{id}',
        summary: 'Delete a time entry',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Time entry external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Time entry deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse
    {
        $entry = $request->user()->timeEntries()->where('external_id', $id)->firstOrFail();
        $entry->delete();

        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/time-entries/current',
        summary: 'Get the currently running timer',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current timer status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'running', type: 'boolean'),
                        new OA\Property(property: 'data', type: 'object', nullable: true),
                    ]
                )
            ),
        ]
    )]
    public function current(Request $request): JsonResponse
    {
        $entry = $request->user()->timeEntries()
            ->whereNull('stopped_at')
            ->with('vector:id,external_id,name,color')
            ->first();

        return response()->json([
            'running' => $entry !== null,
            'data' => $entry ? array_merge($entry->toArray(), [
                'elapsed_seconds' => $entry->started_at->diffInSeconds(now()),
            ]) : null,
        ]);
    }

    #[OA\Post(
        path: '/time-entries',
        summary: 'Start a new timer',
        description: 'Creates a new time entry and starts the timer. Stops any currently running timer.',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'description', type: 'string', example: 'Working on feature X', nullable: true),
                    new OA\Property(property: 'vector_id', type: 'string', format: 'uuid', description: 'Vector external_id', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Timer started'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'vector_id' => 'nullable|string',
        ]);

        // Stop any running entry
        $running = $request->user()->timeEntries()->whereNull('stopped_at')->first();
        if ($running) {
            $running->stopped_at = now();
            $running->save();
        }

        $vectorId = null;
        if ($request->vector_id) {
            $vector = $request->user()->vectors()->where('external_id', $request->vector_id)->first();
            $vectorId = $vector?->id;
        }

        $entry = $request->user()->timeEntries()->create([
            'description' => $request->description,
            'vector_id' => $vectorId,
            'started_at' => now(),
        ]);

        $entry->load('vector:id,external_id,name,color');

        return response()->json(['data' => $entry], 201);
    }

    #[OA\Patch(
        path: '/time-entries/current/stop',
        summary: 'Stop the running timer',
        security: [['bearerAuth' => []]],
        tags: ['Time Entries'],
        responses: [
            new OA\Response(response: 200, description: 'Timer stopped'),
            new OA\Response(response: 404, description: 'No running timer'),
        ]
    )]
    public function stop(Request $request): JsonResponse
    {
        $entry = $request->user()->timeEntries()->whereNull('stopped_at')->first();

        if (! $entry) {
            return response()->json(['message' => 'No running timer.'], 404);
        }

        $entry->stopped_at = now();
        $entry->save();
        $entry->load('vector:id,external_id,name,color', 'tags:id,external_id,name');

        return response()->json([
            'data' => array_merge($entry->toArray(), [
                'duration_seconds' => $entry->started_at->diffInSeconds($entry->stopped_at),
            ]),
        ]);
    }
}
