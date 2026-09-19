<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_user_can_register_login_and_fetch_profile(): void
    {
        $this->get('/sanctum/csrf-cookie');

        $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated()
            ->assertJsonPath('data.email', 'new@example.com');

        $this->post('/api/logout')->assertOk();

        $this->get('/sanctum/csrf-cookie');

        $this->postJson('/api/login', [
            'email' => 'new@example.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('data.email', 'new@example.com');

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', 'new@example.com');
    }

    public function test_admin_ping_requires_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/admin/ping')
            ->assertForbidden();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/admin/ping')
            ->assertOk();
    }
}
