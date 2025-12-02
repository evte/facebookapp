<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'domain',
        'app_name',
        'app_id',
        'app_secret',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the custom columns for the tenant.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'password',
            'domain',
            'app_name',
            'app_id',
            'app_secret',
        ];
    }

    /**
     * Accessor for app_secret to provide secure handling
     */
    protected function appSecret(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => $value,
        );
    }
}
