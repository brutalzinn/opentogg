<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    #[OA\Get(
        path: '/tags',
        summary: 'List all tags',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        responses: [
            new OA\Response(response: 200, description: 'List of tags'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $tags = $request->user()->tags()->orderBy('name')->get();

        return response()->json(['data' => $tags]);
    }

    #[OA\Post(
        path: '/tags',
        summary: 'Create a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'urgent'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tag created'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = $request->user()->tags()->create($data);

        return response()->json(['data' => $tag], 201);
    }

    #[OA\Get(
        path: '/tags/{id}',
        summary: 'Get a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Tag external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, string $id): JsonResponse
    {
        $tag = $request->user()->tags()->where('external_id', $id)->firstOrFail();

        return response()->json(['data' => $tag]);
    }

    #[OA\Put(
        path: '/tags/{id}',
        summary: 'Update a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Tag external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tag updated'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $tag = $request->user()->tags()->where('external_id', $id)->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $tag->update($data);

        return response()->json(['data' => $tag]);
    }

    #[OA\Delete(
        path: '/tags/{id}',
        summary: 'Delete a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Tag external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Tag deleted'),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tag = $request->user()->tags()->where('external_id', $id)->firstOrFail();
        $tag->delete();

        return response()->json(null, 204);
    }
}
