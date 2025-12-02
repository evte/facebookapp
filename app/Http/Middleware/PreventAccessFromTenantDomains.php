<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessFromTenantDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the current host is NOT in the central domains list, block access.
        if (! in_array($request->getHost(), config('tenancy.central_domains'))) {
            abort(404);
        }

        return $next($request);
    }
}
