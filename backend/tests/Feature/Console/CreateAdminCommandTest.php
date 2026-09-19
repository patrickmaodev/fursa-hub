<?php

namespace Tests\Feature\Console;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_and_admin_from_config(): void
    {
        config([
            'admin.role' => 'admin',
            'admin.name' => 'Env Admin',
            'admin.email' => 'env-admin@example.com',
            'admin.password' => 'secret-password-123',
        ]);

        $this->artisan('admin:create')
            ->assertSuccessful()
            ->expectsOutputToContain('env-admin@example.com');

        $user = User::query()->where('email', 'env-admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->isAdmin());
        $this->assertDatabaseHas('admins', ['user_id' => $user->id]);
    }

    public function test_does_not_create_duplicate_admin(): void
    {
        $user = User::factory()->admin()->create(['email' => 'existing@example.com']);

        config([
            'admin.role' => 'admin',
            'admin.name' => 'Duplicate',
            'admin.email' => 'existing@example.com',
            'admin.password' => 'another-password',
        ]);

        $this->artisan('admin:create')
            ->assertSuccessful()
            ->expectsOutputToContain('already exists');

        $this->assertSame(1, Admin::query()->where('user_id', $user->id)->count());
    }

    public function test_grants_admin_to_existing_non_admin_user(): void
    {
        $user = User::factory()->create(['email' => 'promote@example.com']);

        config([
            'admin.role' => 'admin',
            'admin.name' => 'Promote',
            'admin.email' => 'promote@example.com',
            'admin.password' => 'ignored',
        ]);

        $this->artisan('admin:create')->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
