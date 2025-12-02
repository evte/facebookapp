<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Starting tenant user check...\n";

$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "No tenants found in the central database.\n";
    exit;
}

foreach ($tenants as $tenant) {
    echo "--------------------------------------------------\n";
    echo "Checking tenant: " . $tenant->id . "\n";
    
    try {
        tenancy()->initialize($tenant);
        
        $user = User::where('email', 'zj@feiranmedia.com')->first();
        
        if ($user) {
            echo "  [FOUND] User 'zj@feiranmedia.com' exists.\n";
            $user->password = Hash::make('12345678');
            $user->save();
            echo "  [SUCCESS] Password has been reset to '12345678'.\n";
        } else {
            echo "  [MISSING] User 'zj@feiranmedia.com' does NOT exist in this tenant.\n";
            
            // Option: Create the user if missing?
            // For now, let's just report.
            // Uncomment to create:
             echo "  [CREATING] Creating user...\n";
             $user = new User();
             $user->name = 'Zhou Jie';
             $user->email = 'zj@feiranmedia.com';
             $user->password = Hash::make('12345678');
             $user->save();
             echo "  [SUCCESS] User created with password '12345678'.\n";
        }
        
        tenancy()->end();
    } catch (\Exception $e) {
        echo "  [ERROR] Could not initialize tenant or access DB: " . $e->getMessage() . "\n";
    }
}

echo "--------------------------------------------------\n";
echo "Check complete.\n";
