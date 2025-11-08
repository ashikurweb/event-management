<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permission Middleware
 * 
 * Check if user has required permission(s) to access the route
 * 
 * Usage in routes:
 * Route::middleware(['permission:events.create'])->group(...)
 * Route::middleware(['permission:events.create,events.edit'])->group(...)
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // If no permissions specified, allow access (shouldn't happen, but safety check)
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (!$request->user()->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}

