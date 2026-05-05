<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class TogglBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (! str_starts_with($authHeader, 'Basic ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $decoded = base64_decode(substr($authHeader, 6));
        if (! $decoded || ! str_contains($decoded, ':')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        [$token, $suffix] = explode(':', $decoded, 2);

        // Toggl uses {token}:api_token format
        if ($suffix !== 'api_token') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->setUserResolver(fn () => $accessToken->tokenable);

        return $next($request);
    }
}
