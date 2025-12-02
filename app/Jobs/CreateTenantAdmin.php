<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class CreateTenantAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected TenantWithDatabase $tenant
    ) {
    }

    public function handle(): void
    {
        // 在租户上下文中创建管理员用户
        $this->tenant->run(function () {
            $tenant = $this->tenant;

            // 创建管理员用户，使用租户的名称、邮箱和密码
            \App\Models\User::create([
                'name' => $tenant->name,
                'email' => $tenant->email,
                'password' => $tenant->password, // 已经是 hashed 的密码
                'email_verified_at' => \Carbon\Carbon::now(),
            ]);
        });
    }
}
