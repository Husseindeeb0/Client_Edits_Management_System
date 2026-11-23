<?php

namespace App\Features\Authentication\Services;

use App\Features\Authentication\DTOs\ChangePasswordDTO;
use App\Features\Authentication\DTOs\LoginDTO;
use App\Features\Authentication\DTOs\UpdateProfileDTO;
use App\Features\Authentication\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user credentials using the web guard, regenerate the
     * session, and return the authenticated user.
     *
     * @throws ValidationException
     */
    public function login(LoginDTO $dto, Request $request): User
    {
        if (! Auth::guard('web')->attempt(['email' => $dto->email, 'password' => $dto->password])) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::guard('web')->user();

        return $user;
    }

    /**
     * Log the user out of the application, invalidate the session and
     * regenerate the CSRF token.
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Partially update the admin's name and/or email with the validated fields that were sent.
     */
    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $user->update($dto->toArray());

        return $user;
    }

    /**
     * Replace the admin's password. The current password was already checked
     * by the request; the model's `hashed` cast hashes the new one.
     */
    public function changePassword(User $user, ChangePasswordDTO $dto): void
    {
        $user->update(['password' => $dto->newPassword]);
    }
}
