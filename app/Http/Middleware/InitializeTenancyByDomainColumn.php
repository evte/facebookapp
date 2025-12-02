<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomainColumn
{
    protected Tenancy $tenancy;

    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();

        // Check if it's a central domain
        if (in_array($domain, config('tenancy.central_domains'))) {
            return $next($request);
        }

        // Find tenant by domain column
        $tenant = Tenant::where('domain', $domain)->first();

        if (!$tenant) {
            \Log::error('Tenant not found', [
                'domain' => $domain,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            abort(404, 'Tenant not found for domain: ' . $domain);
        }

        // Initialize tenancy
        $this->tenancy->initialize($tenant);

        // 记录租户访问日志
        \Log::info('Tenant initialized', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'domain' => $domain,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'database' => \DB::connection()->getDatabaseName(),
        ]);

        return $next($request);
    }
}
