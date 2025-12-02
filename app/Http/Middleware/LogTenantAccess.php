<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogTenantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 只在登录页记录日志
        if ($request->is('tenant/login')) {
            \Log::info('Tenant login page accessed', [
                'tenant_id' => tenant('id'),
                'tenant_name' => tenant('name'),
                'domain' => $request->getHost(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'database' => \DB::connection()->getDatabaseName(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        return $next($request);
    }
}
