<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Health Check Controller
 *
 * Handles health check endpoint
 */
class HealthController extends Controller
{
    /**
     * Health check endpoint
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
