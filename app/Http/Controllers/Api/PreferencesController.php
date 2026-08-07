<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class PreferencesController extends Controller
{
    #[OA\Get(
        path: '/preferences',
        summary: 'Get the authenticated user preferences',
        security: [['bearerAuth' => []]],
        tags: ['Preferences'],
        responses: [
            new OA\Response(response: 200, description: 'Current preferences'),
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->present($request->user())]);
    }

    #[OA\Patch(
        path: '/preferences',
        summary: 'Update the authenticated user preferences',
        security: [['bearerAuth' => []]],
        tags: ['Preferences'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'locale', type: 'string', example: 'pt-BR'),
                    new OA\Property(property: 'timezone', type: 'string', example: 'America/Sao_Paulo'),
                    new OA\Property(property: 'currency', type: 'string', example: 'BRL'),
                    new OA\Property(property: 'hourly_rate', type: 'number', format: 'float', example: 120.0),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Preferences updated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['sometimes', 'required', Rule::in(array_keys(config('preferences.locales')))],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'currency' => ['sometimes', 'required', Rule::in(config('preferences.currencies'))],
            'hourly_rate' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'message' => 'Preferences updated.',
            'data' => $this->present($user->fresh()),
        ]);
    }

    /**
     * @return array{locale:string,timezone:string,currency:string,hourly_rate:string}
     */
    private function present($user): array
    {
        return [
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'currency' => $user->currency,
            'hourly_rate' => $user->hourly_rate,
        ];
    }
}
