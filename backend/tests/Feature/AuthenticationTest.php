<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Logged in successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'admin@example.com',
                ],
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_response_contains_only_expected_user_fields(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();

        $user = $response->json('user');

        // Only id, name, email should be present
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);

        // Sensitive and unnecessary fields must be absent
        $this->assertArrayNotHasKey('password', $user);
        $this->assertArrayNotHasKey('remember_token', $user);
        $this->assertArrayNotHasKey('created_at', $user);
        $this->assertArrayNotHasKey('updated_at', $user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_request_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_request_validates_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-a-valid-email',
            'password' => 'secret',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_get_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertExactJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_me_response_contains_only_expected_user_fields(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk();

        $userPayload = $response->json('user');

        $this->assertArrayHasKey('id', $userPayload);
        $this->assertArrayHasKey('name', $userPayload);
        $this->assertArrayHasKey('email', $userPayload);
        $this->assertArrayNotHasKey('password', $userPayload);
        $this->assertArrayNotHasKey('remember_token', $userPayload);
        $this->assertArrayNotHasKey('created_at', $userPayload);
        $this->assertArrayNotHasKey('updated_at', $userPayload);
    }

    public function test_get_me_rejects_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);

        $this->assertGuest('web');
    }

    public function test_logout_rejects_unauthenticated_requests(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_login_rate_limiting_works(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'rate-limit@example.com',
                'password' => 'wrong-pass',
            ]);
        }

        $response = $this->postJson('/api/auth/login', [
            'email' => 'rate-limit@example.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(429);
    }

    public function test_admin_seeder_does_not_create_duplicate_users(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->assertEquals(1, User::where('email', env('ADMIN_EMAIL', 'admin@example.com'))->count());

        // Run seeder second time — count must remain 1
        $this->seed(AdminUserSeeder::class);
        $this->assertEquals(1, User::where('email', env('ADMIN_EMAIL', 'admin@example.com'))->count());
    }
}
