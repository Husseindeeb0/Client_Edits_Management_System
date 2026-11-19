<?php

namespace App\Features\Authentication\Controllers;

use App\Features\Authentication\DTOs\LoginDTO;
use App\Features\Authentication\Requests\LoginRequest;
use App\Features\Authentication\Resources\AuthenticatedUserResource;
use App\Features\Authentication\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {
    }

    /**
     * Authenticate administrator and initialize session.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDTO(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        $user = $this->authService->login($dto, $request);

        return (new AuthenticatedUserResource($user))
            ->additional(['message' => 'Logged in successfully.'])
            ->response();
    }

    /**
     * Invalidate session and log user out.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Return the currently authenticated administrator.
     */
    public function me(Request $request): AuthenticatedUserResource|JsonResponse
    {
        // The route has no auth middleware, so ask the Sanctum guard directly
        $user = $request->user('sanctum');

        if ($user === null) {
            return response()->json(['data' => null]);
        }

        return new AuthenticatedUserResource($user);
    }
}
