<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyByDomainColumn;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainColumn::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });

    // 测试路由：检查租户识别和数据库切换
    Route::get('/test-tenant', function () {
        $tenantId = tenant('id');
        $tenantName = tenant('name');
        $dbName = \DB::connection()->getDatabaseName();
        $usersCount = \App\Models\User::count();
        $users = \App\Models\User::all(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'tenant_id' => $tenantId,
            'tenant_name' => $tenantName,
            'database_name' => $dbName,
            'users_count' => $usersCount,
            'users' => $users,
            'request_host' => request()->getHost(),
        ]);
    });

    // Facebook Auth Routes
    Route::get('/facebook/connect', [\App\Http\Controllers\Tenant\FacebookAuthController::class, 'redirect'])->name('tenant.facebook.connect');
    Route::get('/facebook/callback', [\App\Http\Controllers\Tenant\FacebookAuthController::class, 'callback'])->name('tenant.facebook.callback');
});
