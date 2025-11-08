<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Middleware
 * 
 * Check if user has required role(s) to access the route
 * 
 * Usage in routes:
 * Route::middleware(['role:admin'])->group(...)
 * Route::middleware(['role:admin,organizer'])->group(...)
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // If no roles specified, allow access (shouldn't happen, but safety check)
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$request->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

