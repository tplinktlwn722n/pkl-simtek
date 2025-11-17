<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Controller
 *
 * Handles user data operations
 */
class UserController extends Controller
{
    /**
     * Get authenticated user data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User data retrieved successfully',
            'data' => new UserResource($request->user()),
        ], 200);
    }
}
