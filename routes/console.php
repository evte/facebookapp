<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenant:fix-password', function () {
    $this->info("Starting tenant user check...");

    $tenants = Tenant::all();

    if ($tenants->isEmpty()) {
        $this->error("No tenants found in the central database.");
        return;
    }

    foreach ($tenants as $tenant) {
        $this->line("--------------------------------------------------");
        $this->info("Checking tenant: " . $tenant->id);
        
        try {
            tenancy()->initialize($tenant);
            
            $user = User::where('email', 'zj@feiranmedia.com')->first();
            
            if ($user) {
                $this->info("  [FOUND] User 'zj@feiranmedia.com' exists.");
                $user->password = Hash::make('12345678');
                $user->save();
                $this->info("  [SUCCESS] Password has been reset to '12345678'.");
            } else {
                $this->warn("  [MISSING] User 'zj@feiranmedia.com' does NOT exist in this tenant.");
                
                $this->info("  [CREATING] Creating user...");
                $user = new User();
                $user->name = 'Zhou Jie';
                $user->email = 'zj@feiranmedia.com';
                $user->password = Hash::make('12345678');
                $user->save();
                $this->info("  [SUCCESS] User created with password '12345678'.");
            }
            
            tenancy()->end();
        } catch (\Exception $e) {
            $this->error("  [ERROR] Could not initialize tenant or access DB: " . $e->getMessage());
        }
    }

    $this->line("--------------------------------------------------");
    $this->info("Check complete.");
});