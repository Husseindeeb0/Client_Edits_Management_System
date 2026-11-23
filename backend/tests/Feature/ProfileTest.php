<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('old-password'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Update profile
    // -------------------------------------------------------------------------

    public function test_admin_can_update_name_and_email(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', ['name' => 'New Name', 'email' => 'new@example.com'])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $this->admin->user_id,
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $this->admin->user_id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_admin_can_update_only_the_name(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', ['name' => 'Only Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Only Name')
            ->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_admin_can_keep_their_own_email(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', ['name' => 'Admin', 'email' => 'admin@example.com'])
            ->assertOk();
    }

    public function test_update_profile_rejects_an_email_used_by_another_account(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', ['email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_profile_validates_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', ['name' => '', 'email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_update_profile_requires_at_least_one_field(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson('/api/auth/profile', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payload']);
    }

    public function test_update_profile_rejects_guests(): void
    {
        $this->patchJson('/api/auth/profile', ['name' => 'Guest'])->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // Change password
    // -------------------------------------------------------------------------

    public function test_admin_can_change_password(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/auth/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
        ])
            ->assertOk()
            ->assertExactJson(['message' => 'Password changed successfully.']);

        $this->assertTrue(Hash::check('new-password', $this->admin->fresh()->password));
    }

    public function test_change_password_rejects_a_wrong_current_password(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/auth/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password', $this->admin->fresh()->password));
    }

    public function test_change_password_rejects_the_same_password(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/auth/password', [
            'current_password' => 'old-password',
            'new_password' => 'old-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_requires_at_least_8_characters(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/auth/password', [
            'current_password' => 'old-password',
            'new_password' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_requires_both_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/auth/password', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password', 'new_password']);
    }

    public function test_change_password_rejects_guests(): void
    {
        $this->putJson('/api/auth/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
        ])->assertUnauthorized();
    }
}
