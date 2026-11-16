<?php

namespace Database\Seeders;

use App\Features\Authentication\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Password used only outside production when ADMIN_PASSWORD is not set.
     */
    public const DEVELOPMENT_PASSWORD = 'password123';

    /**
     * Run the database seeds.
     *
     * @throws RuntimeException When run in production without a real password.
     */
    public function run(): void
    {
        $password = config('auth.admin.password');

        if (app()->isProduction() && (blank($password) || $password === self::DEVELOPMENT_PASSWORD)) {
            throw new RuntimeException(
                'Set a strong ADMIN_PASSWORD in the environment before seeding the admin account in production.'
            );
        }

        User::updateOrCreate(
            ['email' => config('auth.admin.email')],
            [
                'name' => config('auth.admin.name'),
                // Hashed automatically by the User model's "hashed" cast
                'password' => $password ?: self::DEVELOPMENT_PASSWORD,
            ]
        );
    }
}
