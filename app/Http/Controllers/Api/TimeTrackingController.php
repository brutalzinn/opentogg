<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OpenTogg API',
    description: 'Time tracking REST API. Authenticate with a Bearer token created in Settings.',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
class TimeTrackingController extends Controller {}
