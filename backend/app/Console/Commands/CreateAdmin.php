<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:create')]
#[Description('Create the initial administrator from ADMIN_* environment variables')]
class CreateAdmin extends Command
{
    private const WEAK_PASSWORDS = [
        'change-this-password',
        'password',
        'temporary-password',
    ];

    public function handle(): int
    {
        $role = Str::lower((string) config('admin.role', 'admin'));
        if ($role !== 'admin') {
            $this->components->error('ADMIN_ROLE must be "admin". Other roles are not supported yet.');

            return self::FAILURE;
        }

        $name = trim((string) config('admin.name', ''));
        $email = Str::lower(trim((string) config('admin.email', '')));
        $password = (string) config('admin.password', '');

        if ($name === '' || $email === '' || $password === '') {
            $this->components->error('Set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD in your .env file.');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('ADMIN_EMAIL is not a valid email address.');

            return self::FAILURE;
        }

        if (in_array(Str::lower($password), self::WEAK_PASSWORDS, true)) {
            $this->components->warn('ADMIN_PASSWORD looks like a placeholder. Use a strong password before production.');
        }

        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            if ($user->admin()->exists()) {
                $this->components->warn("Administrator [{$email}] already exists. No duplicate was created.");

                return self::SUCCESS;
            }

            Admin::query()->create(['user_id' => $user->id]);
            $this->components->info("Existing user [{$email}] was granted administrator access.");

            return self::SUCCESS;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        Admin::query()->create(['user_id' => $user->id]);

        $this->components->info("Administrator [{$email}] created successfully.");
        $this->components->bulletList([
            'Sign in at /admin/login (no public admin registration).',
            'Remove ADMIN_PASSWORD from .env after provisioning in production.',
        ]);

        return self::SUCCESS;
    }
}
