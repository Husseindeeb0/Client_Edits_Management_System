<?php

namespace Tests\Feature;

use App\Features\Authentication\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requests_are_limited_to_60_per_minute(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/clients')->assertOk();
        }

        $this->getJson('/api/clients')
            ->assertTooManyRequests()
            ->assertHeader('Retry-After');
    }

    public function test_limit_is_shared_across_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 60; $i++) {
            $this->getJson($i % 2 === 0 ? '/api/clients' : '/api/edits')->assertOk();
        }

        $this->getJson('/api/companies')->assertTooManyRequests();
    }
}
