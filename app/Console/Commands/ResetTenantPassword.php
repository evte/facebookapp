<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetTenantPassword extends Command
{
    protected $signature = 'tenant:reset-password {domain} {email} {password}';
    protected $description = 'Reset a tenant user password';

    public function handle()
    {
        $domain = $this->argument('domain');
        $email = $this->argument('email');
        $password = $this->argument('password');

        $tenant = Tenant::where('domain', $domain)->first();

        if (!$tenant) {
            $this->error("Tenant not found for domain: {$domain}");
            return 1;
        }

        $this->info("Found tenant: {$tenant->name} ({$tenant->id})");

        $tenant->run(function () use ($email, $password) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("User not found: {$email}");
                return;
            }

            $user->password = Hash::make($password);
            $user->save();

            $this->info("Password updated for: {$user->name} ({$user->email})");
            $this->info("New password: {$password}");
        });

        return 0;
    }
}
