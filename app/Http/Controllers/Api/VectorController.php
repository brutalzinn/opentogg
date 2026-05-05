<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VectorController extends Controller
{
    #[OA\Get(
        path: '/vectors',
        summary: 'List all vectors',
        security: [['bearerAuth' => []]],
        tags: ['Vectors'],
        responses: [
            new OA\Response(response: 200, description: 'List of vectors'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $vectors = $request->user()->vectors()->orderBy('name')->get();

        return response()->json(['data' => $vectors]);
    }

    #[OA\Post(
        path: '/vectors',
        summary: 'Create a vector',
        security: [['bearerAuth' => []]],
        tags: ['Vectors'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Work'),
                    new OA\Property(property: 'color', type: 'string', example: '#BB86FC'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Vector created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $vector = $request->user()->vectors()->create([
            'name' => $data['name'],
            'color' => $data['color'] ?? '#6B7280',
        ]);

        return response()->json(['data' => $vector], 201);
    }

    #[OA\Get(
        path: '/vectors/{id}',
        summary: 'Get a vector',
        security: [['bearerAuth' => []]],
        tags: ['Vectors'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Vector external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Vector details'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, string $id): JsonResponse
    {
        $vector = $request->user()->vectors()->where('external_id', $id)->firstOrFail();

        return response()->json(['data' => $vector]);
    }

    #[OA\Put(
        path: '/vectors/{id}',
        summary: 'Update a vector',
        security: [['bearerAuth' => []]],
        tags: ['Vectors'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Vector external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'color', type: 'string', example: '#BB86FC'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Vector updated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $vector = $request->user()->vectors()->where('external_id', $id)->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|size:7',
        ]);

        $vector->update($data);

        return response()->json(['data' => $vector]);
    }

    #[OA\Delete(
        path: '/vectors/{id}',
        summary: 'Delete a vector',
        security: [['bearerAuth' => []]],
        tags: ['Vectors'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Vector external_id', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Vector deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse
    {
        $vector = $request->user()->vectors()->where('external_id', $id)->firstOrFail();
        $vector->delete();

        return response()->json(null, 204);
    }
}
